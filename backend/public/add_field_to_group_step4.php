<?php
session_start();

require_once dirname(__DIR__) . '/config.php';
require_once BACKEND_ROOT . '/src/DTO/Template.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

// Validar template_id
$templateId = $_POST['template_id'] ?? null;
if (!$templateId) {
    die('template_id no proporcionado');
}

// Validar grupo activo
if (!isset($_SESSION['current_group']) || $_SESSION['current_group'] === null) {
    die('No hay un grupo activo');
}

// ===============================
// CONSTRUIR CAMPO DEL GRUPO
// ===============================
$field = [
    'type' => 'field',
    'name' => $_POST['field_name'],
    'representation' => $_POST['representation']
];

// ===============================
// TRADUCIR UI → BACKEND
// ===============================
switch ($_POST['origin_type']) {

    case 'cfdi':
        // El usuario escribió "Cantidad"
        // Internamente se guarda como cfdi.cantidad
        $field['source'] = 'cfdi.' . strtolower(trim($_POST['cfdi_field']));
        break;

    case 'fixed':
        $field['value'] = $_POST['fixed_value'];
        break;

    case 'calculation':
        $field['calculation'] = $_POST['calculation'];
        break;
}

// ===============================
// AGREGAR CAMPO AL GRUPO EN SESIÓN
// ===============================
$_SESSION['current_group']['children'][] = $field;

// Volver al Step 4
header(
    'Location: /addendas/frontend/wizard_step4.php?template_id=' .
    urlencode($templateId)
);
exit;
