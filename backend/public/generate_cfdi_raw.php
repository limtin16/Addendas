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

    if (strpos($xml, '<cfdi:Addenda') === 0) {

        // ✅ extraer OPEN TAG COMPLETO
        if (preg_match('/^(<cfdi:Addenda[^>]*>)([\s\S]*)(<\/cfdi:Addenda>)$/', $xml, $m)) {

            $openTag = $m[1];   // mantiene namespace ✅
            $inner   = $m[2];
            $closeTag= $m[3];

            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;

            libxml_use_internal_errors(true);
            // ✅ envolver solo contenido
            if ($doc->loadXML('<root>' . $inner . '</root>')) {

                $formatted = $doc->saveXML($doc->documentElement);

                $formatted = preg_replace('/^<root>|<\/root>$/', '', $formatted);

                // ✅ reconstruir SIN perder namespace
                return $openTag . trim($formatted) . $closeTag;
            }
        }
    }

    return $xml;
}


function detectIndentUnit(string $xml): string
{
    if (preg_match('/\n([ \t]+)<cfdi:/', $xml, $m)) {

        $indent = $m[1];

        // ✅ detectar si es tab
        if (strpos($indent, "\t") !== false) {
            return "\t";
        }

        // ✅ detectar número de espacios (2, 4, etc.)
        preg_match('/^ +/', $indent, $spaces);
        return $spaces[0] ?? "  ";
    }

    return "  "; // fallback
}

function findSemanticInsertPosition(string $xml): int
{
    // ✅ si hay Complemento → insertar después
    if (preg_match('/<\/cfdi:Complemento>/', $xml, $m, PREG_OFFSET_CAPTURE)) {
        return $m[0][1] + strlen($m[0][0]);
    }

    // ✅ si no hay Complemento → antes del cierre
    if (preg_match('/<\/cfdi:Comprobante>/', $xml, $m, PREG_OFFSET_CAPTURE)) {
        return $m[0][1];
    }

    return -1;
}


function detectIndentationStyle(string $xml): string
{
    if (preg_match('/\n([ \t]+)<cfdi:/', $xml, $m)) {
        return $m[1]; // puede ser espacios o tabs
    }

    return "    "; // fallback (4 espacios)
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
    $xml = preg_replace('/>\s*</', ">\n<", trim($xml));

    $lines = explode("\n", $xml);

    $level = 0;
    $result = [];

    foreach ($lines as $line) {

        $trim = trim($line);

        if (preg_match('/^<\/.+>$/', $trim)) {
            $level--;
        }

        $result[] = $baseIndent
            . str_repeat($indentUnit, max($level, 0))
            . $trim;

        if (preg_match('/^<[^\/!?][^>]*[^\/]>$/', $trim)) {
            $level++;
        }
    }

    return implode("\n", $result);
}

function removeExistingAddenda(string $xml): string
{
    return preg_replace(
        '/\n?[ \t]*<cfdi:Addenda[\s\S]*?<\/cfdi:Addenda>/',
        '',
        $xml
    );
}

function processAddendaForInsert(string $originalCfdi, string $newAddendaXml): string
{
    $mode = $_SESSION['addenda_mode'] ?? 'manual';

    $indentBase = detectLastChildIndent($originalCfdi);
    $indentUnit = detectIndentUnit($originalCfdi);

    // ✅ limpiar Addenda anterior
    $originalCfdi = removeExistingAddenda($originalCfdi);

    // ✅ formateo
    if ($mode !== 'xml') {
        $newAddendaXml = prettyXml($newAddendaXml);
    } else {
        $newAddendaXml = trim($newAddendaXml);
    }

    // ✅ limpiar líneas basura tipo ">" suelta
    $newAddendaXml = preg_replace('/^\s*>\s*$/m', '', $newAddendaXml);

    // ✅ reindentar correctamente
    $formattedAddenda = reindentXml(
        $newAddendaXml,
        $indentBase,
        $indentUnit
    );

    // ✅ posición semántica
    $pos = findSemanticInsertPosition($originalCfdi);

    if ($pos === -1) {
        return $originalCfdi;
    }

    $before = substr($originalCfdi, 0, $pos);
    $after  = substr($originalCfdi, $pos);

    $before = rtrim($before, "\r\n") . "\n";

    $final = $before
    . rtrim($formattedAddenda)
    . PHP_EOL
    . ltrim($after);

    return $final;
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

$xmlInput = trim($_POST['addenda_xml']);

// ✅ tomar namespace del template
$templateNs = $_POST['addenda_namespace'] ?? '';
$templateNs = trim($templateNs);

// ✅ normalizar si no tiene xmlns
if ($templateNs && !str_contains($templateNs, 'xmlns')) {
    $templateNs = 'xmlns:' . ltrim($templateNs);
}

// ✅ construir SIEMPRE el tag correcto
$openTag = '<cfdi:Addenda';

if ($templateNs !== '') {
    $openTag .= ' ' . $templateNs;
}

$openTag .= '>';

// ✅ REESCRIBIR SIEMPRE el tag de apertura
$newAddendaXml = preg_replace(
    '/<cfdi:Addenda[^>]*>/',
    $openTag,
    $xmlInput,
    1
);

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

exit;