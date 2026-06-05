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
$templateServicePath = $path . "backend/src/Services/TemplateService.php";
$path.="backend/config.php";
require_once $path;
require_once $templateServicePath;
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\AddendaXmlBuilder;
use App\Services\TemplateService;

$service = new TemplateService();

/* =======================================================
   1. Validar archivo
   ======================================================= */

if (
    !isset($_FILES['addenda_xml']) ||
    $_FILES['addenda_xml']['error'] !== UPLOAD_ERR_OK
) {
    die('❌ Error al subir el archivo XML.');
}

$xmlContent = file_get_contents($_FILES['addenda_xml']['tmp_name']);
if (!$xmlContent || trim($xmlContent) === '') {
    die('❌ El archivo XML está vacío.');
}

/* =======================================================
   2. Cargar XML original (CFDI o Addenda)
   ======================================================= */

libxml_use_internal_errors(true);

$originalDom = new DOMDocument('1.0', 'UTF-8');
if (!$originalDom->loadXML($xmlContent)) {
    die('❌ El archivo no es un XML válido.');
}

// ✅ detectar namespace real del CFDI (cfdi)
$cfdiNs = '';

// ✅ 1. intentar desde Addenda
foreach ($addendaWrapper->attributes as $attr) {
    if ($attr->prefix === 'xmlns' && $attr->localName === 'cfdi') {
        $cfdiNs = $attr->nodeValue;
        break;
    }
}

// ✅ 2. fallback desde Comprobante
if (!$cfdiNs) {
    $comprobante = $originalDom->documentElement;

    if ($comprobante instanceof DOMElement) {
        foreach ($comprobante->attributes as $attr) {
            if ($attr->prefix === 'xmlns' && $attr->localName === 'cfdi') {
                $cfdiNs = $attr->nodeValue;
                break;
            }
        }
    }
}

// ✅ validación (opcional pero útil)
if (!$cfdiNs) {
    // no rompas ejecución, pero loggea
    error_log('No se pudo detectar namespace CFDI');
}

/* =======================================================
   3. EXTRAER Addenda
   ======================================================= */

$xpathOriginal = new DOMXPath($originalDom);

$addendaWrapper = $xpathOriginal
    ->query('//*[local-name()="Addenda"]')
    ->item(0);

if (!$addendaWrapper) {
    die('❌ No se encontró Addenda');
}

foreach ($addendaWrapper->attributes as $attr) {

    if ($attr->prefix === 'xmlns' && $attr->localName === 'cfdi') {
        $cfdiNs = $attr->nodeValue;
        break;
    }
}

// ✅ TOMAR EL HIJO REAL (IMPORTANTÍSIMO)
$realAddendaNode = null;

foreach ($addendaWrapper->childNodes as $child) {
    if ($child instanceof DOMElement) {
        $realAddendaNode = $child;
        break;
    }
}

if (!$realAddendaNode) {
    die('❌ La Addenda no tiene contenido');
}

// ✅ guardar CFDI completo para autofill
//$_SESSION['original_cfdi_xml'] = $originalDom->saveXML();

/* =======================================================
   4. CREAR DOM LIMPIO SOLO CON ADDENDA
   ======================================================= */

$addendaDom = new DOMDocument('1.0', 'UTF-8');

$cleanAddenda = $addendaDom->importNode($realAddendaNode, true);
$addendaDom->appendChild($cleanAddenda);

// ✅ ahora sí XPath sobre addenda
$xpath = new DOMXPath($addendaDom);

/* =======================================================
   5. PARSEAR ADDENDA (SIN VALORES)
   ======================================================= */

function parseAddendaNode(DOMElement $element): array
{
    $node = [
        'type' => 'node',
        'name' => $element->localName,
        'prefix' => $element->prefix,
        'namespace' => $element->namespaceURI,
        'children' => []

    ];

    // ✅ 1. NAMESPACES (solo guardar)
    if ($element->hasAttributes()) {
        foreach ($element->attributes as $attr) {
            if (strpos($attr->nodeName, 'xmlns') === 0) {
                $node['namespaces'][$attr->nodeName] = $attr->nodeValue;
            }
        }
    }

    // ✅ 2. ATRIBUTOS (CORRECTO)
    if ($element->hasAttributes()) {
        foreach ($element->attributes as $attr) {

            // ❌ ignorar xmlns
            if (strpos($attr->nodeName, 'xmlns') === 0) {
                continue;
            }

            $node['children'][] = [
                'type' => 'field',
                'name' => $attr->localName, // ✅ SIN @
                'origin' => [
                    'type' => 'fixed',
                    'value' => ''
                ],
                'representation' => 'attribute'
            ];
        }
    }

    // ✅ 3. HIJOS RECURSIVOS
    foreach ($element->childNodes as $child) {
        if ($child instanceof DOMElement) {
            $node['children'][] = parseAddendaNode($child);
        }
    }

    return $node;
}

$addendaRoot = $addendaDom->documentElement;

$structure = parseAddendaNode($addendaRoot);

/* =======================================================
   6. GENERAR TEMPLATE XML (LIMPIO)
   ======================================================= */

$builder = new AddendaXmlBuilder();

$rootName = $addendaRoot->localName; // ✅ SIN prefijo
$prefix = $addendaRoot->prefix ?: '';
$namespace = $addendaRoot->namespaceURI ?: '';

$builderStructure = [
    'root' => [
        'name' => $rootName,
        'prefix' => $prefix,
        'namespace' => $namespace,
        'children' => $structure['children'] ?? []
    ]
];

$addendaXmlTemplate = $builder->build($builderStructure);

// ✅ construir INSTANCE (igual que wizard)
function convertNode($node)
{
    if (!is_array($node)) return null;

    $type = $node['type'] ?? '';

    if ($type === 'field') {
        return [
            'type' => 'field',
            'name' => $node['name']
        ];
    }

    if ($type === 'node') {
        return [
            'type' => 'node',
            'name' => $node['name'],
            'children' => array_values(array_filter(
                array_map('convertNode', $node['children'] ?? [])
            ))
        ];
    }

    return null;
}

// ✅ root original
$root = [
    'name' => $rootName,
    'prefix' => $prefix,
    'namespace' => $namespace,
    'children' => $structure['children'] ?? []
];

// ✅ INSTANCE ESTÁNDAR
$instance = [
    'type' => 'node',
    'name' => $rootName,
    'children' => array_values(array_filter(
        array_map('convertNode', $root['children'])
    ))
];
/* =======================================================
   7. GUARDAR EN DB y genera ID
   ======================================================= */

$template = $service->save(
    'Addenda Upload',
    'ADDENDA',
    [
        'root' => [
            'name' => $rootName,
            'prefix' => $prefix,
            'namespace' => $namespace,
            'children' => $structure['children'],
            'instance' => $instance,
            'addenda_xml_template' => $addendaXmlTemplate,
            // ✅ CLAVE (igual que modo manual)
            'addenda_extra_ns' => $cfdiNs
        ]
    ]
);

// ✅ AQUÍ obtienes el ID REAL
$templateId = $template->id;
/* =======================================================
   8. REDIRIGIR
   ======================================================= */
header("Location: " . BASE_URL . "/frontend/render_instance_form.php?template_id=" . urlencode($templateId));
exit;