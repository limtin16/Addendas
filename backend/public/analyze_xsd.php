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
require_once BACKEND_ROOT . '/src/Services/XsdToArrayConverter.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\TemplateService;
use App\Services\AddendaXmlBuilder;
use App\Services\XsdToArrayConverter;

$service = new TemplateService();

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
    die('❌ No se subió archivo XSD');
}

$xsd = file_get_contents($_FILES['xsd_file']['tmp_name']);

if (!$xsd) {
    die('❌ XSD vacío');
}

/* =======================================================
   2. CONVERTIR XSD → STRUCTURE
   ======================================================= */

$converter = new XsdToArrayConverter();
$structure = $converter->convert($xsd);
normalizeNodeTypes($structure);

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

header("Location: " . BASE_URL . "/frontend/render_instance_form.php?template_id=" . urlencode($templateId));
exit;