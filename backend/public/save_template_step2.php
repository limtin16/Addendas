<?php

define('BASE_PATH', dirname(__DIR__));

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
require_once BASE_PATH . '/src/DTO/Template.php';
require_once BASE_PATH . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

// ===============================
// ✅ LEER DATOS
// ===============================
$templateId = $_POST['template_id'] ?? '';
$rootName   = trim($_POST['root_name'] ?? '');
$prefix     = trim($_POST['prefix'] ?? '');
$namespace  = trim($_POST['namespace'] ?? '');


// ===============================
// ✅ VALIDAR
// ===============================
if ($templateId === '' || $rootName === '' || $namespace === '') {
    die('❌ Datos inválidos. Regresa e intenta de nuevo.');
}

// ===============================
// ✅ OBTENER TEMPLATE
// ===============================
$service = new TemplateService();
$template = $service->get($templateId);

$addendaExtraNs = trim($_POST['addenda_extra_ns'] ?? '');

// ===============================
// ✅ ACTUALIZAR ROOT
// ===============================
$template->structure['root']['name'] = $rootName;
$template->structure['root']['prefix'] = $prefix !== '' ? $prefix : null;
$template->structure['root']['namespace'] = $namespace;
$template->structure['root']['addenda_extra_ns'] = $addendaExtraNs;

// 👇 CRÍTICO: asegurar children SIEMPRE
if (!isset($template->structure['root']['children']) || !is_array($template->structure['root']['children'])) {
    $template->structure['root']['children'] = [];
}

// ===============================
// ✅ GUARDAR
// ===============================
$service->update($templateId, $template->structure);
// ✅ SOLO guardar template (NO mezclar con instance)
$_SESSION['addenda_instance'] = $template->structure;

// ===============================
// ✅ REDIRIGIR A STEP 3
// ===============================
header("Location: " . BASE_URL . "/frontend/wizard_step3.php?template_id=" . urlencode($templateId));
exit;