<?php
session_start();

/* =======================================================
   Validar archivo
   ======================================================= */

if (!isset($_FILES['target_cfdi'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibió archivo']);
    exit;
}

$file = $_FILES['target_cfdi'];

/* ===============================
   ✅ VALIDACIONES DE SEGURIDAD
================================ */

// ✅ error upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Error al subir archivo']);
    exit;
}

// ✅ tamaño (2MB)
if ($file['size'] > 2 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'Archivo demasiado grande']);
    exit;
}

// ✅ extensión
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($extension !== 'xml') {
    http_response_code(400);
    echo json_encode(['error' => 'Solo se permiten archivos XML']);
    exit;
}

// ✅ MIME (CRÍTICO)
$mime = mime_content_type($file['tmp_name']);
$allowedMime = ['text/xml', 'application/xml', 'application/x-xml'];

if (!in_array($mime, $allowedMime)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de archivo inválido']);
    exit;
}

$xmlContent = file_get_contents($_FILES['target_cfdi']['tmp_name']);

if (!$xmlContent || trim($xmlContent) === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'El CFDI destino está vacío'
    ]);
    exit;
}

/* =======================================================
   ✅ VALIDAR TIPO CFDI
   ======================================================= */

libxml_use_internal_errors(true);

libxml_use_internal_errors(true);

if (strpos($xmlContent, '<!ENTITY') !== false) {
    http_response_code(400);
    echo json_encode(['error' => 'XML contiene entidades no permitidas']);
    exit;
}

$xml = simplexml_load_string(
    $xmlContent,
    "SimpleXMLElement",
    LIBXML_NONET
);

if (!$xml) {
    http_response_code(400);
    echo json_encode([
        'error' => 'XML inválido, el XML debe ser tipo Ingreso o Egreso'
    ]);
    exit;
}

if (!$xml) {
    http_response_code(400);
    echo json_encode([
        'error' => 'XML inválido, el XML debe ser tipo Ingreso o Egreso'
    ]);
    exit;
}

/**
 * Manejo de namespaces (muy importante en CFDI)
 */
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
    http_response_code(400);
    echo json_encode([
        'error' => 'No se encontró el nodo Comprobante'
    ]);
    exit;
}

/* =======================================================
   ⚠️ VALIDAR CANCELACIÓN (requiere SAT)
   ======================================================= */

// Aquí deberías llamar al servicio del SAT
// Por ahora no se valida realmente

// Ejemplo futuro:
// if ($estatus === 'Cancelado') { ... }

/* =======================================================
   ✅ VALIDAR QUE ESTÉ TIMBRADO (UUID)
   ======================================================= */

$uuid = null;

// Registrar namespace tfd si existe
if (isset($namespaces['tfd'])) {
    $xml->registerXPathNamespace('tfd', $namespaces['tfd']);
    $timbre = $xml->xpath('//tfd:TimbreFiscalDigital');
} else {
    $timbre = $xml->xpath('//TimbreFiscalDigital');
}

if (!$timbre || !isset($timbre[0])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'El CFDI no está timbrado (no tiene UUID)'
    ]);
    exit;
}

$uuid = (string)$timbre[0]['UUID'];

if (!$uuid) {
    http_response_code(400);
    echo json_encode([
        'error' => 'El CFDI no contiene UUID válido'
    ]);
    exit;
}

/* =======================================================
   ✅ VALIDAR QUE NO TENGA ADDENDA
   ======================================================= */

// Buscar nodo Addenda (con o sin namespace)
if (isset($namespaces['cfdi'])) {
    $xml->registerXPathNamespace('cfdi', $namespaces['cfdi']);
    $addendaNodes = $xml->xpath('//cfdi:Addenda');
} else {
    $addendaNodes = $xml->xpath('//Addenda');
}

// Si ya tiene addenda → bloquear
if (!empty($addendaNodes)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'El CFDI cargado ya contiene una addenda'
    ]);
    exit;
}

