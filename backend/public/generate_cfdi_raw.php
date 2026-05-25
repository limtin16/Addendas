<?php
session_start();

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '../db.php';
require_once dirname(__DIR__) . '../src/Services/CreditService.php';

$userId = $_SESSION['user_id'] ?? null;
$isGuestPaid = $_SESSION['guest_paid'] ?? false;

if (!$userId && !$isGuestPaid) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$creditService = new CreditService($conn);

// ✅ SOLO validar créditos si es usuario logueado
if ($userId) {

    $available = $creditService->getAvailableCredits($userId);

    if ($available <= 0) {
        echo json_encode([
            'error' => 'No tienes créditos disponibles'
        ]);
        exit;
    }

}

header('Content-Type: application/json');

// ===============================
// ✅ VALIDAR CFDI
// ===============================
if (
    !isset($_FILES['cfdi']) ||
    $_FILES['cfdi']['error'] !== UPLOAD_ERR_OK
) {
    echo json_encode([
        'error' => 'CFDI no proporcionado'
    ]);
    exit;
}

$originalCfdi = file_get_contents($_FILES['cfdi']['tmp_name']);

if (!$originalCfdi || trim($originalCfdi) === '') {
    echo json_encode([
        'error' => 'CFDI vacío'
    ]);
    exit;
}

// ===============================
// ✅ VALIDAR ADDENDA
// ===============================
if (
    !isset($_POST['addenda_xml']) ||
    trim($_POST['addenda_xml']) === ''
) {
    echo json_encode([
        'error' => 'Addenda no recibida'
    ]);
    exit;
}

$newAddendaXml = trim($_POST['addenda_xml']);

// ===============================
// ✅ DETECTAR NAMESPACE CFDI
// ===============================
$cfdiNamespace = 'http://www.sat.gob.mx/cfd/4';

if (preg_match('/xmlns:cfdi="([^"]+)"/', $originalCfdi, $matches)) {
    $cfdiNamespace = $matches[1];
}

// ===============================
// ✅ INSERTAR ADDENDA CON DOM
// ===============================
libxml_use_internal_errors(true);

$doc = new DOMDocument('1.0', 'UTF-8');

if (!$doc->loadXML($originalCfdi)) {
    echo json_encode([
        'error' => 'CFDI inválido'
    ]);
    exit;
}

// crear nodo Addenda
$addendaNode = $doc->createElementNS($cfdiNamespace, 'cfdi:Addenda');

// insertar XML como fragmento
$fragment = $doc->createDocumentFragment();

if (!$fragment->appendXML($newAddendaXml)) {
    echo json_encode([
        'error' => 'Addenda XML inválido'
    ]);
    exit;
}

$addendaNode->appendChild($fragment);

// ubicar comprobante
$xpath = new DOMXPath($doc);
$xpath->registerNamespace('cfdi', $cfdiNamespace);

$comprobante = $xpath->query('//cfdi:Comprobante')->item(0);

if (!$comprobante) {
    echo json_encode([
        'error' => 'Comprobante no encontrado'
    ]);
    exit;
}

// insertar
$comprobante->appendChild($addendaNode);

$finalCfdi = $doc->saveXML();

// ✅ VALIDACIÓN FINAL (muy importante)
if (!$finalCfdi || trim($finalCfdi) === '') {
    echo json_encode([
        'error' => 'Error generando CFDI'
    ]);
    exit;
}

// ✅ SOLO consumir créditos si es usuario
if ($userId) {

    if (!$creditService->consumeOne(
        $userId,
        'Generación de CFDI con Addenda'
    )) {
        echo json_encode([
            'error' => 'No se pudo consumir el crédito'
        ]);
        exit;
    }

} else {
    // ✅ visitante: solo permitir UNA vez
    unset($_SESSION['guest_paid']);
}


// ✅ marcar que generó CFDI
$_SESSION['cfdi_generated'] = true;

// respuesta
echo json_encode([
    'xml' => $finalCfdi
]);
unset($_SESSION['addenda_instance']);
unset($_SESSION['target_cfdi_xml']);

exit;