<?php
session_start();
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$path.="backend/config.php";
require_once $path;
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\TemplateService;
use App\Services\AddendaXmlBuilder;

$service = new TemplateService();

/* =======================================================
   1. VALIDAR
   ======================================================= */

if (!isset($_FILES['addenda_xml'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibió archivo']);
    exit;
}

$file = $_FILES['addenda_xml'];

/* ===============================
   ✅ VALIDACIONES DE SEGURIDAD
================================ */

// ✅ error upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Error al subir archivo']);
    exit;
}

// ✅ límite tamaño (2MB)
if ($file['size'] > 2 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'Archivo demasiado grande']);
    exit;
}

// ✅ validar extensión
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($extension !== 'xml') {
    http_response_code(400);
    echo json_encode(['error' => 'Solo se permiten archivos .xml']);
    exit;
}

// ✅ validar MIME (CRÍTICO)
$mime = mime_content_type($file['tmp_name']);

$allowedMime = [
    'text/xml',
    'application/xml',
    'application/x-xml',
    'text/plain',              // ✅ agregado
    'application/octet-stream' // ✅ agregado
];

// ✅ validar MIME flexible
if (!in_array($mime, $allowedMime)) {

    // ✅ fallback: validar contenido XML real
    $contentSample = file_get_contents($file['tmp_name'], false, null, 0, 200);

    if (
        strpos($contentSample, '<') === false ||
        strpos($contentSample, '>') === false
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de archivo inválido']);
        exit;
    }
}

$xmlContent = file_get_contents($file['tmp_name']);

if (!$xmlContent || trim($xmlContent) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'XML vacío']);
    exit;
}

/* =======================================================
   2. LOAD XML
   ======================================================= */

libxml_use_internal_errors(true);

$dom = new DOMDocument('1.0', 'UTF-8');

// ✅ bloquear conexiones externas
if (!$dom->loadXML($xmlContent, LIBXML_NONET)) {
    http_response_code(400);
    echo json_encode(['error' => 'XML inválido']);
    exit;
}

/* =======================================================
   3. DETECTAR ADDENDA COMPLETA
   ======================================================= */

$xpath = new DOMXPath($dom);

$addendaNode = $xpath
    ->query('//*[local-name()="Addenda"]')
    ->item(0);

if (!$addendaNode) {
    http_response_code(400);
    echo json_encode([
        'error' => 'No hay Addenda'
    ]);
    exit;
}

/* =======================================================
   ✅ CLAVE: TOMAR TODO EL XML INTERNO SIN TOCAR
   ======================================================= */

$innerXml = '';

foreach ($addendaNode->childNodes as $child) {
    $innerXml .= $dom->saveXML($child);
}

$innerXml = trim($innerXml);

if ($innerXml === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Addenda vacía']);
    exit;
}

/* =======================================================
   4. DETECTAR NAMESPACE CFDI REAL (CORRECTO)
   ======================================================= */

$cfdiNs = '';

// ✅ buscar Addenda original como string
if (preg_match('/<([a-zA-Z0-9_]+:)?Addenda\b([^>]*)>/i', $xmlContent, $matches)) {

    $attrs = $matches[2] ?? '';

    // ✅ buscar específicamente xmlns:cfdi SOLO en ese tag
    if (preg_match('/xmlns:cfdi="([^"]+)"/i', $attrs, $nsMatch)) {
        $cfdiNs = $nsMatch[1];
    }
}


/* =======================================================
   5. PARSEAR SOLO PARA GENERAR FORM (STRUCTURE)
   ======================================================= */

function parseNode(DOMElement $el) {
    $node = [
        'type' => 'node',
        'name' => $el->localName,
        'children' => []
    ];

    // atributos → fields
    foreach ($el->attributes ?? [] as $attr) {
        if (strpos($attr->nodeName, 'xmlns') === 0) continue;

        $node['children'][] = [
            'type' => 'field',
            'name' => $attr->localName
        ];
    }

    // hijos
    foreach ($el->childNodes as $child) {
        if ($child instanceof DOMElement) {
            $node['children'][] = parseNode($child);
        }
    }

    return $node;
}

$first = null;
foreach ($addendaNode->childNodes as $child) {
    if ($child instanceof DOMElement) {
        $first = $child;
        break;
    }
}

if (!$first) {
    http_response_code(400);
    echo json_encode(['error' => 'Addenda inválida']);
    exit;
}

/* =======================================================
   6. CREAR INSTANCE (FORM)
   ======================================================= */

$instance = parseNode($first);

/* =======================================================
   7. GUARDAR TEMPLATE
   ======================================================= */

$template = $service->save(
    'Addenda Upload',
    'ADDENDA',
    [
        'root' => [

            // ✅ estos sí los usa el sistema
            'name' => $first->localName,
            'prefix' => $first->prefix,
            'namespace' => $first->namespaceURI,

            // ✅ FORM
            'instance' => $instance,

            // ✅ 🔥 CLAVE REAL
            // guardamos el XML ORIGINAL TAL CUAL
            'addenda_xml_template' => $innerXml,

            // ✅ namespace CFDI REAL
            'addenda_extra_ns' => $cfdiNs
        ]
    ]
);

$templateId = $template->id;

/* =======================================================
   8. REDIRECT
   ======================================================= */
echo json_encode([
    'ok' => true,
    'redirect' => BASE_URL . "/frontend/render_instance_form.php?template_id=" . urlencode($templateId)
]);
exit;