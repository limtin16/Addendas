<?php

define('BASE_PATH', dirname(__DIR__));

$path = "";
$depth = substr_count(__DIR__, DIRECTORY_SEPARATOR) - substr_count(__DIR__, DIRECTORY_SEPARATOR) + substr_count(substr(__DIR__, strpos(__DIR__, 'addendas')), DIRECTORY_SEPARATOR);
for ($i = 0; $i < $depth; $i++) {
    $path .= "../";
}
$path .= "backend/config.php";
require_once $path;
require_once BASE_PATH . '/src/DTO/Template.php';
require_once BASE_PATH . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

// ===============================
// ✅ LEER DATOS
// ===============================
$templateId = $_POST['template_id'] ?? '';
$fieldName  = trim($_POST['field_name'] ?? '');
$representation = $_POST['representation'] ?? 'node';

// ===============================
// ✅ VALIDAR
// ===============================
if ($templateId === '' || $fieldName === '') {
    die('❌ Datos inválidos. Regresa e intenta de nuevo.');
}

// ===============================
// ✅ OBTENER TEMPLATE
// ===============================
$service = new TemplateService();
$template = $service->get($templateId);

if (!$template) {
    die('❌ Template no encontrado.');
}

// ===============================
// ✅ CREAR FIELD (SOLO ESTRUCTURA)
// ===============================
$fieldNode = [
    'type' => 'field',
    'name' => $fieldName,
    'representation' => $representation
];

// ✅ NO origin / NO value / NO calculation

// ===============================
// ✅ AGREGAR AL ROOT
// ===============================
$template->structure['root']['children'][] = $fieldNode;

// ===============================
// ✅ GUARDAR
// ===============================
$service->update($templateId, $template->structure);

// ===============================
// ✅ REDIRIGIR (SEGUIR AGREGANDO)
// ===============================
header('Location: " . BASE_URL . "/frontend/wizard_step3.php?template_id=' . urlencode($templateId));
exit;