<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

function respond($arr, $code = 200) {
    http_response_code($code);
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($arr);
    exit;
}

session_start();

/* =======================================================
   ✅ VALIDAR ARCHIVO
======================================================= */

if (!isset($_FILES['target_cfdi'])) {
    respond(['error' => 'No se recibió archivo'], 400);
}

$file = $_FILES['target_cfdi'];

/* =======================================================
   ✅ VALIDACIONES DE SEGURIDAD
======================================================= */

if ($file['error'] !== UPLOAD_ERR_OK) {
    respond(['error' => 'Error al subir archivo'], 400);
}

if ($file['size'] > 2 * 1024 * 1024) {
    respond(['error' => 'Archivo demasiado grande'], 400);
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($extension !== 'xml') {
    respond(['error' => 'Solo se permiten archivos XML'], 400);
}

$mime = mime_content_type($file['tmp_name']);
$allowedMime = ['text/xml', 'application/xml', 'application/x-xml'];

if (!in_array($mime, $allowedMime)) {
    respond(['error' => 'Tipo de archivo inválido'], 400);
}

$xmlContent = file_get_contents($file['tmp_name']);

if (!$xmlContent || trim($xmlContent) === '') {
    respond(['error' => 'El CFDI destino está vacío'], 400);
}

/* =======================================================
   ✅ VALIDAR XML
======================================================= */

if (strpos($xmlContent, '<!ENTITY') !== false) {
    respond(['error' => 'XML contiene entidades no permitidas'], 400);
}

libxml_use_internal_errors(true);

$xml = simplexml_load_string(
    $xmlContent,
    "SimpleXMLElement",
    LIBXML_NONET
);

if (!$xml) {
    respond([
        'error' => 'XML inválido, el XML debe ser tipo Ingreso o Egreso'
    ], 400);
}

/* =======================================================
   ✅ NAMESPACE
======================================================= */

$namespaces = $xml->getNamespaces(true);
if (!is_array($namespaces)) {
    $namespaces = [];
}

if (isset($namespaces['cfdi'])) {
    $xml->registerXPathNamespace('cfdi', $namespaces['cfdi']);
    $nodes = $xml->xpath('//cfdi:Comprobante');
} else {
    $nodes = $xml->xpath('//Comprobante');
}

if (!$nodes || !isset($nodes[0])) {
    respond(['error' => 'No se encontró el nodo Comprobante'], 400);
}

/* =======================================================
   ✅ VALIDAR TIMBRE (UUID)
======================================================= */

if (isset($namespaces['tfd'])) {
    $xml->registerXPathNamespace('tfd', $namespaces['tfd']);
    $timbre = $xml->xpath('//tfd:TimbreFiscalDigital');
} else {
    $timbre = $xml->xpath('//TimbreFiscalDigital');
}

if (!$timbre || !isset($timbre[0])) {
    respond(['error' => 'El CFDI no está timbrado (no tiene UUID)'], 400);
}

$uuid = (string)$timbre[0]['UUID'];

if (!$uuid) {
    respond(['error' => 'El CFDI no contiene UUID válido'], 400);
}

/* =======================================================
   ✅ VALIDAR ADDENDA
======================================================= */

if (isset($namespaces['cfdi'])) {
    $xml->registerXPathNamespace('cfdi', $namespaces['cfdi']);
    $addendaNodes = $xml->xpath('//cfdi:Addenda');
} else {
    $addendaNodes = $xml->xpath('//Addenda');
}

if (!empty($addendaNodes)) {
    respond(['error' => 'El CFDI cargado ya contiene una addenda'], 400);
}

$dom = new DOMDocument();
$dom->loadXML($xmlContent);

$fields = [];

function walkNode(DOMElement $el, string $path, array &$out)
{
    foreach ($el->attributes as $attr) {
        if ($attr->prefix === 'xmlns') continue;

        $out[] = [
            'path'  => $path . '.@' . $attr->name,
            'label' => $path . ' → @' . $attr->name,
            'value' => $attr->value
        ];
    }

    foreach ($el->childNodes as $child) {
        if ($child instanceof DOMElement) {
            walkNode(
                $child,
                $path . '.' . $child->nodeName,
                $out
            );
        }
    }
}

walkNode(
    $dom->documentElement,
    $dom->documentElement->nodeName,
    $fields
);

/* =======================================================
   ✅ TODO OK
======================================================= */

respond([
    'success' => true,
    'uuid' => $uuid,
    'fields' => $fields
]);