<?php
header('Content-Type: application/json');
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
require_once BACKEND_ROOT . '/src/Services/XsdToArrayConverter.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\TemplateService;
use App\Services\AddendaXmlBuilder;
use App\Services\XsdToArrayConverter;

$service = new TemplateService();

function mapXsdType($type) {
    $type = strtolower($type);

    if (strpos($type, 'int') !== false ||
        strpos($type, 'decimal') !== false ||
        strpos($type, 'float') !== false ||
        strpos($type, 'double') !== false) {
        return 'number';
    }

    if (strpos($type, 'dateTime') !== false) {
        return 'datetime';
    }

    if (strpos($type, 'date') !== false) {
        return 'date';
    }

    if (strpos($type, 'boolean') !== false) {
        return 'boolean';
    }

    return 'string';
}

function enrichTypesFromXsd(&$node)
{
    if (!is_array($node)) return;

    // ✅ SOLO campos
    if (($node['type'] ?? '') === 'field') {

        // caso 1: viene type directo del converter
        $xsdType = $node['xsd_type'] ?? null;

        if ($xsdType) {
            $node['type_data'] = mapXsdType($xsdType);
        }

        // ✅ ENUM DETECTADO (si el converter lo trae)
        if (!empty($node['options'])) {
            $node['type_data'] = 'enum';
        }

        // ✅ seguridad
        if (empty($node['type_data'])) {
            $node['type_data'] = 'string';
        }
    }

    // recursivo
    if (!empty($node['children'])) {
        foreach ($node['children'] as &$child) {
            enrichTypesFromXsd($child);
        }
    }
}

function normalizeNodeTypes(&$node)
{
    if (!is_array($node)) return;

    // 🔥 CASO CRÍTICO: nodo sin hijos → convertir a field
    if (
        ($node['type'] ?? '') === 'node' &&
        empty($node['children'])
    ) {
        $node['type'] = 'field';

        // asegurar tipo
        if (empty($node['type_data'])) {
            $node['type_data'] = 'string';
        }
    }

    // recursivo
    if (!empty($node['children'])) {
        foreach ($node['children'] as &$child) {
            normalizeNodeTypes($child);
        }
    }
}

/* =======================================================
   4. CONVERTIR A INSTANCE (FORM)
   ======================================================= */

function convertNode($node)
{
    if (!is_array($node)) return null;

    $type = $node['type'] ?? '';

    if ($type === 'field') {

        // ✅ AQUÍ está el fix real
        $typeData = $node['type_data'] ?? '';
        if (empty($typeData)) {
            $typeData = 'string';
        }

        return [
            'type' => 'field',
            'name' => $node['name'],
            'type_data' => $typeData,
            'options' => $node['options'] ?? []
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

/* =======================================================
   1. VALIDAR XSD
   ======================================================= */

if (!isset($_FILES['xsd_file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No se subió archivo XSD']);
    exit;
}

$xsd = file_get_contents($_FILES['xsd_file']['tmp_name']);

if (!$xsd) {
    http_response_code(400);
    echo json_encode(['error' => 'El XSD está vacío']);
    exit;
}

libxml_use_internal_errors(true);

$xml = simplexml_load_string($xsd);

if (!$xml) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo no es un XML válido']);
    exit;
}

$rootName = $xml->getName();

if (stripos($rootName, 'schema') === false) {
    http_response_code(400);
    echo json_encode([
        'error' => 'El archivo no es un XSD válido (no contiene schema)'
    ]);
    exit;
}

$namespaces = $xml->getNamespaces(true);

$validXsdNs = false;

foreach ($namespaces as $ns) {
    if (strpos($ns, 'XMLSchema') !== false) {
        $validXsdNs = true;
        break;
    }
}

if (!$validXsdNs) {
    http_response_code(400);
    echo json_encode([
        'error' => 'El XSD no tiene namespace válido'
    ]);
    exit;
}

/* =======================================================
   2. CONVERTIR XSD → STRUCTURE
   ======================================================= */

$converter = new XsdToArrayConverter();
$structure = $converter->convert($xsd);

if (empty($structure) || empty($structure['children'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'El XSD no contiene estructura utilizable'
    ]);
    exit;
}

normalizeNodeTypes($structure);

// 🔥 NUEVO
enrichTypesFromXsd($structure);

/* =======================================================
   3. GENERAR XML TEMPLATE (BASE)
   ======================================================= */

$builder = new AddendaXmlBuilder();

//esto puede que duplique la etiquta addenda
$addendaXmlTemplate = $builder->build([
    'children' => [$structure] // ✅ modo XSD
]);

/* =======================================================
   5. CREAR INSTANCE ROOT
   ======================================================= */

$rootNode = $structure;

$instance = [
    'type' => 'node',
    'name' => $rootNode['name'] ?? 'Addenda',
    'children' => array_values(array_filter(
        array_map('convertNode', $rootNode['children'] ?? [])
    ))
];

/* =======================================================
   6. EXTRAER DATOS BASE
   ======================================================= */

$rootName = $rootNode['name'] ?? 'Addenda';
$prefix = $rootNode['prefix'] ?? '';
$namespace = $rootNode['namespace'] ?? '';

/* =======================================================
   7. GUARDAR TEMPLATE
   ======================================================= */

$template = $service->save(
    'Addenda XSD',
    'ADDENDA',
    [
        'root' => [
            'name' => $rootName,
            'prefix' => $prefix,
            'namespace' => $namespace,

            // ✅ estructura base
            'children' => $rootNode['children'] ?? [],

            // ✅ form
            'instance' => $instance,

            // ✅ XML base
            'addenda_xml_template' => $addendaXmlTemplate,

            // ✅ IMPORTANTE: XSD no tiene cfdi namespace
            'addenda_extra_ns' => '' 
        ]
    ]
);

$templateId = $template->id;

/* =======================================================
   8. REDIRIGIR
   ======================================================= */

echo json_encode([
    'ok' => true,
    'redirect' => BASE_URL . "/frontend/render_instance_form.php?template_id=" . urlencode($templateId)
]);
exit;