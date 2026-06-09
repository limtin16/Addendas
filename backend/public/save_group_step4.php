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

// ✅ CLASES
require_once BACKEND_ROOT . '/src/DTO/Template.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\TemplateService;
use App\Services\AddendaXmlBuilder;

// ===============================
// ✅ CONVERTIR A INSTANCE
// ===============================
function convertNode($node)
{
    if (!is_array($node)) return null;

    $type = $node['type'] ?? '';

    // =========================
    // ✅ FIELD
    // =========================
    if ($type === 'field') {
        return [
            'type' => 'field',
            'name' => $node['name'] ?? ''
        ];
    }

    // =========================
    // ✅ GROUP (🔥 CORREGIDO)
    // =========================
    if ($type === 'group') {
        return [
            'type' => 'group', // ✅ YA NO node
            'name' => $node['name'] ?? '',
            'item_name' => $node['item_name'] ?? 'Item',
            'children' => array_values(array_filter(
                array_map('convertNode', $node['children'] ?? [])
            ))
        ];
    }

    // =========================
    // ✅ NODE NORMAL (ROOT)
    // =========================
    if ($type === 'node') {
        return [
            'type' => 'node',
            'name' => $node['name'] ?? '',
            'children' => array_values(array_filter(
                array_map('convertNode', $node['children'] ?? [])
            ))
        ];
    }

    return null;
}

// ===============================
// ✅ DETECTAR SI ES FINALIZACIÓN
// ===============================
$isFinalizing = !empty($_POST['redirect_done']);

// ===============================
// ✅ VALIDAR template_id
// ===============================
$templateId = $_POST['template_id'] ?? null;

if (!$templateId) {
    die('template_id no proporcionado');
}
// ===============================
// ✅ VALIDAR GRUPO (solo si NO finaliza)
// ===============================
$currentGroupJson = $_POST['current_group'] ?? '';
$currentGroup = $currentGroupJson
    ? json_decode($currentGroupJson, true)
    : null;

if (!$isFinalizing && !$currentGroup) {
    die('No hay grupo activo para guardar');
}

// ===============================
// ✅ OBTENER TEMPLATE
// ===============================
$service = new TemplateService();
$template = $service->get($templateId);

if (!$template) {
    die('Template no encontrado');
}

// ===============================
// ✅ CASO 1: GUARDAR GRUPO
// ===============================
if (!$isFinalizing) {

    // asegurar estructura válida
    if (!isset($currentGroup['children'])) {
        $currentGroup['children'] = [];
    }

    // ✅ agregar grupo al template

    $template->structure['root']['children'][] = $currentGroup;

    // ✅ guardar template
    $service->update($templateId, $template->structure);

    // ✅ regresar a step4
    header("Location: " . BASE_URL . "/frontend/wizard_step4.php?template_id=" . urlencode($templateId));
    exit;
}

// ===============================
// ✅ CASO 2: FINALIZAR ADDENDA
// ===============================

$builder = new AddendaXmlBuilder();
$root = $template->structure['root'] ?? [];

if (empty($root['name'])) {
    die('❌ Root sin nombre');
}

// ✅ generar XML base (TEMPLATE)
$addendaXmlTemplate = $builder->build($template->structure);

$root = $template->structure['root'] ?? [];

$instanceStructure = [
    'type' => 'node',
    'name' => $root['name'] ?? 'Addenda',
    'children' => array_values(array_filter(
        array_map('convertNode', $root['children'] ?? [])
    ))
];

// ===============================
// ✅ GUARDAR INSTANCE EN TEMPLATE
// ===============================
$template->structure['root']['instance'] = $instanceStructure;
$template->structure['root']['addenda_xml_template'] = $addendaXmlTemplate;
$service->update($templateId, $template->structure);

// ===============================
// ✅ REDIRIGIR A FORM FINAL
// ===============================
header("Location: " . BASE_URL . "/frontend/render_instance_form.php?template_id=" . urlencode($templateId));
exit;