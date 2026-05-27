<?php

define('BASE_PATH', dirname(__DIR__));

require_once dirname(__DIR__) . '/config.php';
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

if (!$template) {
    die('❌ Template no encontrado.');
}

// ===============================
// ✅ ASEGURAR ESTRUCTURA
// ===============================
if (!isset($template->structure['root'])) {
    $template->structure['root'] = [];
}

// ===============================
// ✅ ACTUALIZAR ROOT
// ===============================
$template->structure['root']['name'] = $rootName;
$template->structure['root']['prefix'] = $prefix !== '' ? $prefix : null;
$template->structure['root']['namespace'] = $namespace;

// 👇 CRÍTICO: asegurar children SIEMPRE
if (!isset($template->structure['root']['children']) || !is_array($template->structure['root']['children'])) {
    $template->structure['root']['children'] = [];
}

// ===============================
// ✅ GUARDAR
// ===============================
$service->update($templateId, $template->structure);

// ===============================
// ✅ REDIRIGIR A STEP 3
// ===============================
header('Location: /addendas/frontend/wizard_step3.php?template_id=' . urlencode($templateId));
exit;