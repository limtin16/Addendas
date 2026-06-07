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
require_once BACKEND_ROOT . '/src/DTO/Template.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

// ===============================
// VALIDAR template_id
// ===============================
$templateId = $_POST['template_id'] ?? null;

if (!$templateId) {
    die('template_id no proporcionado');
}

// ===============================
// ✅ RECIBIR current_group DESDE POST
// ===============================
$currentGroupJson = $_POST['current_group'] ?? '';

$currentGroup = $currentGroupJson
    ? json_decode($currentGroupJson, true)
    : null;

if (!$currentGroup) {
    die('No hay un grupo activo');
}

// ===============================
// ✅ CREAR CAMPO
// ===============================
$fieldName = trim($_POST['field_name'] ?? '');
$representation = $_POST['representation'] ?? 'attribute';

if ($fieldName === '') {
    die('Nombre de campo requerido');
}

// ===============================
// ✅ AGREGAR AL GRUPO
// ===============================
$currentGroup['children'][] = [
    'type' => 'field',
    'name' => $fieldName,
    'representation' => $representation
];

// ===============================
// ✅ REDIRECT CON GRUPO SERIALIZADO
// ===============================
$encodedGroup = urlencode(json_encode($currentGroup));

header(
    "Location: " .BASE_URL . "/frontend/wizard_step4.php?template_id=" .
    urlencode($templateId) .
    "&current_group=" . $encodedGroup
);

exit;