<?php

session_start();

$path = "";
$depth = substr_count(__DIR__, DIRECTORY_SEPARATOR) - substr_count(__DIR__, DIRECTORY_SEPARATOR) + substr_count(substr(__DIR__, strpos(__DIR__, 'addendas')), DIRECTORY_SEPARATOR);
for ($i = 0; $i < $depth; $i++) {
    $path .= "../";
}
$path .= "backend/config.php";
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
// VALIDAR grupo activo
// ===============================
if (!isset($_SESSION['current_group']) || $_SESSION['current_group'] === null) {
    die('No hay un grupo activo');
}

// ===============================
// CREAR CAMPO (SOLO ESTRUCTURA)
// ===============================
$fieldName = trim($_POST['field_name'] ?? '');
$representation = $_POST['representation'] ?? 'attribute';

if ($fieldName === '') {
    die('Nombre de campo requerido');
}

$field = [
    'type' => 'field',
    'name' => $fieldName,
    'representation' => $representation
];

// ✅ IMPORTANTE: NO origin / NO value / NO calculation

// ===============================
// AGREGAR AL GRUPO EN SESSION
// ===============================
$_SESSION['current_group']['children'][] = [
    'type' => 'field',
    'name' => $fieldName,
    'representation' => $representation
];

// ===============================
// REGRESAR A STEP 4
// ===============================
header(
    'Location: " . BASE_URL . "/frontend/wizard_step4.php?template_id=' .
    urlencode($templateId)
);
exit;