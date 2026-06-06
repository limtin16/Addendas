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

if (
    !isset($_FILES['addenda_xml']) ||
    $_FILES['addenda_xml']['error'] !== UPLOAD_ERR_OK
) {
    die('❌ Error al subir XML');
}

$xmlContent = file_get_contents($_FILES['addenda_xml']['tmp_name']);

if (!$xmlContent || trim($xmlContent) === '') {
    die('❌ XML vacío');
}

/* =======================================================
   2. LOAD XML
   ======================================================= */

$dom = new DOMDocument('1.0', 'UTF-8');
libxml_use_internal_errors(true);

if (!$dom->loadXML($xmlContent)) {
    die('❌ XML inválido');
}

/* =======================================================
   3. DETECTAR ADDENDA COMPLETA
   ======================================================= */

$xpath = new DOMXPath($dom);

$addendaNode = $xpath
    ->query('//*[local-name()="Addenda"]')
    ->item(0);

if (!$addendaNode) {
    die('❌ No hay Addenda');
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
    die('❌ Addenda vacía');
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
    die('❌ Addenda inválida');
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

<<<<<<< HEAD
$template->structure['root'] = [
    'structure' => $structure,
    'addenda_xml_template' => $addendaXmlTemplate,
    'origin' => 'upload',
    'uploaded_at' => date('c')
];


$_SESSION['addenda_instance'] = $template->structure;

/* =======================================================
   8. REDIRIGIR
   ======================================================= */

header("Location: " . BASE_URL . "/frontend/render_instance_form.php");
=======
header("Location: " . BASE_URL . "/frontend/render_instance_form.php?template_id=" . urlencode($templateId));
>>>>>>> rescue-namespace
exit;