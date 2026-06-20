<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

function respond($arr, $code = 200) {
    http_response_code($code);
    while (ob_get_level()) ob_end_clean();
    echo json_encode($arr);
    exit;
}

/* =====================================================
   ✅ VALIDAR INPUTS
===================================================== */

if (!isset($_FILES['xml']) || !isset($_FILES['xsd'])) {
    respond(['error' => 'Debe subir XML y XSD'], 400);
}

$xmlFile = $_FILES['xml'];
$xsdFile = $_FILES['xsd'];

if ($xmlFile['error'] !== UPLOAD_ERR_OK || $xsdFile['error'] !== UPLOAD_ERR_OK) {
    respond(['error' => 'Error al subir archivos'], 400);
}

/* =====================================================
   ✅ SEGURIDAD BÁSICA
===================================================== */

// tamaño máximo
if ($xmlFile['size'] > 3 * 1024 * 1024 || $xsdFile['size'] > 3 * 1024 * 1024) {
    respond(['error' => 'Archivos demasiado grandes'], 400);
}

// extensiones
if (
    strtolower(pathinfo($xmlFile['name'], PATHINFO_EXTENSION)) !== 'xml' ||
    strtolower(pathinfo($xsdFile['name'], PATHINFO_EXTENSION)) !== 'xsd'
) {
    respond(['error' => 'Tipos de archivo inválidos'], 400);
}

$xmlContent = file_get_contents($xmlFile['tmp_name']);
$xsdPath = $xsdFile['tmp_name'];

/* =====================================================
   ✅ PROTECCIÓN ANTI XXE
===================================================== */

if (strpos($xmlContent, '<!ENTITY') !== false) {
    respond(['error' => 'XML contiene entidades no permitidas'], 400);
}

libxml_use_internal_errors(true);

/* =====================================================
   ✅ CARGAR XML
===================================================== */

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;

if (!$dom->loadXML($xmlContent, LIBXML_NONET)) {

    $errors = libxml_get_errors();
    libxml_clear_errors();

    respond([
        'error' => 'XML inválido',
        'details' => array_map(fn($e) => trim($e->message), $errors)
    ], 400);
}

/* =====================================================
   ✅ VALIDAR CON XSD
===================================================== */

/* =====================================================
   ✅ EXTRAER SOLO ADDENDA
===================================================== */

$xpath = new DOMXPath($dom);
$xpath->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');

$addendaNode = $xpath->query('//cfdi:Addenda')->item(0);

if (!$addendaNode) {
    respond([
        'valid' => false,
        'error' => 'El XML no contiene nodo Addenda'
    ]);
}

/* =====================================================
   ✅ CREAR XML SOLO CON ADDENDA
===================================================== */

$addendaChild = null;

// buscar primer elemento hijo real (ignora textos)
foreach ($addendaNode->childNodes as $child) {
    if ($child->nodeType === XML_ELEMENT_NODE) {
        $addendaChild = $child;
        break;
    }
}

if (!$addendaChild) {
    respond([
        'valid' => false,
        'error' => 'La Addenda no contiene elementos válidos'
    ]);
}

$newDom = new DOMDocument('1.0', 'UTF-8');
$newDom->appendChild(
    $newDom->importNode($addendaChild, true)
);

/* =====================================================
   ✅ VALIDAR ADDENDA CONTRA XSD
===================================================== */

if (!$newDom->schemaValidate($xsdPath)) {

    $errors = libxml_get_errors();
    libxml_clear_errors();

    $formattedErrors = [];

    foreach ($errors as $e) {
        $formattedErrors[] = [
            'line' => $e->line,
            'message' => trim($e->message)
        ];
    }

    respond([
        'valid' => false,
        'errors' => $formattedErrors
    ]);
}

/* =====================================================
   ✅ TODO OK
===================================================== */

respond([
    'valid' => true,
    'message' => 'Addenda válida contra el XSD'
]);

/* =====================================================
   ✅ TODO OK
===================================================== */

respond([
    'valid' => true,
    'message' => 'XML cumple con el XSD'
]);