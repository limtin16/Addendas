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

header('Content-Type: application/json');

// ===============================
// ✅ VALIDAR template_id
// ===============================
$templateId = $_GET['template_id'] ?? null;

if (!$templateId) {
    http_response_code(400);
    echo json_encode(['error' => 'template_id requerido']);
    exit;
}

// ===============================
// ✅ OBTENER TEMPLATE
// ===============================
$service = new TemplateService();
$template = $service->get($templateId);

if (!$template) {
    http_response_code(404);
    echo json_encode(['error' => 'Template no encontrado']);
    exit;
}

// ===============================
// ✅ ASEGURAR ROOT
// ===============================
if (
    !isset($template->structure) ||
    empty($template->structure['name'])
) {
    echo json_encode([
        'structurePreview' => '❌ Root inválido'
    ]);
    exit;
}

// ===============================
// ✅ INCLUIR GRUPO ACTUAL (preview)
// ===============================
if (
    isset($_SESSION['current_group']) &&
    is_array($_SESSION['current_group']) &&
    !empty($_SESSION['current_group'])
) {

    $group = $_SESSION['current_group'];

    if (!isset($group['type'])) {
        $group['type'] = 'group';
    }

    if (!isset($group['children']) || !is_array($group['children'])) {
        $group['children'] = [];
    }

    if (!isset($group['item_name'])) {
        $group['item_name'] = 'Item';
    }

    $template->structure['children'][] = $group;
}

// ===============================
// ✅ GENERAR XML (SOLO BUILDER)
// ===============================
$builder = new AddendaXmlBuilder();
$xmlBase = $builder->build($template->structure);

if (!$xmlBase) {
    echo json_encode([
        'structurePreview' => '❌ Error generando XML'
    ]);
    exit;
}

// ===============================
// ✅ RESPUESTA FINAL
// ===============================
echo json_encode([
    'structurePreview' => $xmlBase
]);

exit;