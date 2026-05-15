<?php

define('BASE_PATH', dirname(__DIR__));

require_once dirname(__DIR__) . '/config.php';
require_once BASE_PATH . '/src/DTO/Template.php';
require_once BASE_PATH . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

// Leer datos
$templateId     = $_POST['template_id'] ?? '';
$fieldName      = trim($_POST['field_name'] ?? '');
$representation = $_POST['representation'] ?? 'node';
$source         = trim($_POST['source'] ?? '');

// Validaciones
if ($templateId === '' || $fieldName === '') {
    die('❌ Datos inválidos. Regresa e intenta de nuevo.');
}

$service = new TemplateService();
$template = $service->get($templateId);

if (!$template) {
    die('❌ Template no encontrado.');
}

// ✅ Crear definición del campo
$fieldNode = [
    'type' => 'field',
    'name' => $fieldName,
    'representation' => $representation,
];
switch ($_POST['origin_type'] ?? null) {

    case 'cfdi':

    $cfdiField = trim($_POST['cfdi_field'] ?? '');

    if ($cfdiField === '') {
        // ✅ NO guardar source inválido
        break;
    }

    $fieldNode['source'] = 'cfdi.' . strtolower($cfdiField);
    break;

    case 'fixed':
        $fieldNode['value'] = $_POST['fixed_value'];
        break;

    case 'calculation':
        $fieldNode['calculation'] = $_POST['calculation'];
        break;
}

if ($source !== '') {
    $fieldNode['source'] = $source;
}

// ✅ Agregar al root
$template->structure['root']['children'][] = $fieldNode;

// ✅ Guardar
$service->update($templateId, $template->structure);

// ✅ REDIRIGIR De neuvo al paso 3 PARA MAS CAMPOS
header('Location: /addendas/frontend/wizard_step3.php?template_id=' . urlencode($templateId));
exit;
