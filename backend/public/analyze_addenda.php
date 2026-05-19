<?php
session_start();

require_once dirname(__DIR__) . '/config.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\AddendaXmlBuilder;

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
$_SESSION['original_cfdi_xml'] = $originalDom->saveXML();

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
        'name' => $element->localName, // ✅ importante
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

/* =======================================================
   7. GUARDAR EN SESSION (INSTANCIA LIMPIA)
   ======================================================= */

$_SESSION['addenda_instance'] = [
    'structure' => $structure,
    'addenda_xml_template' => $addendaXmlTemplate,
    'origin' => 'upload',
    'uploaded_at' => date('c')
];

/* =======================================================
   8. REDIRIGIR
   ======================================================= */

header('Location: /addendas/frontend/render_instance_form.php');
exit;