<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$dbPath = $path . "backend/db.php";
$creditsPath = $path . "backend/src/Services/CreditService.php";
$path.="backend/config.php";
require_once $path;
require_once $dbPath;
require_once $creditsPath;
session_start();

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
$isGuestPaid = $_SESSION['guest_paid'] ?? false;

if (!$userId && !$isGuestPaid) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

function prettyXml($xml) {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;

    if (!$doc->loadXML($xml)) {
        return $xml;
    }

    return $doc->saveXML($doc->documentElement);
}

function extractInnerAddenda(string $xml): string
{
    if (strpos($xml, '<cfdi:Addenda') === false) {
        return $xml;
    }

    $doc = new DOMDocument();

    if (!$doc->loadXML($xml)) {
        return $xml;
    }

    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');

    $node = $xpath->query('//cfdi:Addenda')->item(0);

    if (!$node) {
        return $xml;
    }

    $inner = '';

    foreach ($node->childNodes as $child) {
        $inner .= $doc->saveXML($child);
    }

    return trim($inner);
}

function indentXmlContent(string $xml, string $baseIndent): string
{
    $lines = explode("\n", trim($xml));

    $result = [];

    foreach ($lines as $line) {
        $result[] = $baseIndent . $line;
    }

    return implode("\n", $result);
}

function detectIndentationStyle(string $xml): string
{
    if (preg_match('/\n([ \t]+)<cfdi:/', $xml, $m)) {
        return $m[1]; // puede ser espacios o tabs
    }

    return "    "; // fallback (4 espacios)
}

function findInsertPosition(string $xml): int
{
    // buscar cierre de comprobante
    $pos = strpos($xml, '</cfdi:Comprobante>');

    return $pos !== false ? $pos : -1;
}

function detectLastChildIndent(string $xml): string
{
    if (preg_match('/\n([ \t]+)<\/cfdi:Comprobante>/', $xml, $m)) {
        return $m[1];
    }

    return detectIndentationStyle($xml);
}

function reindentXml(string $xml, string $baseIndent, string $indentUnit): string
{
    // ✅ CLAVE: separar etiquetas en líneas
    $xml = preg_replace('/>\s*</', ">\n<", trim($xml));

    $lines = explode("\n", $xml);

    $level = 0;
    $result = [];

    foreach ($lines as $line) {

        $trim = trim($line);

        // ✅ cierre → bajar nivel antes
        if (preg_match('/^<\/.+>$/', $trim)) {
            $level--;
        }

        $result[] = $baseIndent . str_repeat($indentUnit, max($level, 0)) . $trim;

        // ✅ apertura → subir nivel después
        if (
            preg_match('/^<[^\/!?][^>]*[^\/]>$/', $trim)
        ) {
            $level++;
        }
    }

    return implode("\n", $result);
}

function removeExistingAddenda(string $xml): string
{
    return preg_replace(
        '/<cfdi:Addenda[\s\S]*?<\/cfdi:Addenda>/',
        '',
        $xml
    );
}

function processAddendaForInsert(string $originalCfdi, string $newAddendaXml): string
{
    $mode = $_SESSION['addenda_mode'] ?? 'manual';

    // ✅ detectar estilo real
    $indentBase = detectLastChildIndent($originalCfdi);
    $indentUnit = substr($indentBase, 0, 1) === "\t" ? "\t" : "  ";

    // ✅ limpiar addenda previa SI existe
    $originalCfdi = removeExistingAddenda($originalCfdi);

    // ✅ formatear dependiendo del modo
    if ($mode !== 'xml') {
        $newAddendaXml = prettyXml($newAddendaXml);
    } else {
        $newAddendaXml = trim($newAddendaXml);
    }

    // ✅ reindentar conforme al CFDI
    $formattedAddenda = reindentXml(
        $newAddendaXml,
        $indentBase,
        $indentUnit
    );

    // ✅ encontrar posición real
    $pos = findInsertPosition($originalCfdi);

    if ($pos === -1) {
        return $originalCfdi; // fallback
    }

    // ✅ insertar EXACTAMENTE en el punto correcto
    $before = substr($originalCfdi, 0, $pos);
    $after  = substr($originalCfdi, $pos);

    // ✅ eliminar saltos extra antes de insertar
    $before = rtrim($before, "\r\n") . "\n";

    return $before
        . $formattedAddenda . "\n"
        . $after;
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

$finalCfdi = processAddendaForInsert(
    $originalCfdi,
    $newAddendaXml
);

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