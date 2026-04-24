<?php

define('BASE_PATH', dirname(__DIR__));

require_once dirname(__DIR__) . '/config.php';
require_once BASE_PATH . '/src/DTO/Template.php';
require_once BASE_PATH . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

// Leer datos
$templateId = $_POST['template_id'] ?? '';
$rootName   = trim($_POST['root_name'] ?? '');
$prefix     = trim($_POST['prefix'] ?? '');
$namespace  = trim($_POST['namespace'] ?? '');

// Validaciones
$templateId = $_POST['template_id'] ?? '';$namespace  = trim($_POST['namespace'] ?? '');

if ($templateId === '' || $rootName === '' || $namespace === '') {
    die('❌ Datos inválidos. Regresa e intenta de nuevo.');
}

$rootName   = trim($_POST['root_name'] ?? '');
$prefix     = trim($_POST['prefix'] ?? '');


$service = new TemplateService();
$template = $service->get($templateId);

if (!$template) {
    die('❌ Template no encontrado.');
}

// ✅ Actualizar SOLO el root
$template->structure['root']['name']      = $rootName;
$template->structure['root']['prefix'] = $prefix ?: null;
$template->structure['root']['namespace'] = $namespace;

// ✅ Guardar (SIN pasar template_id)
$service->update($templateId, $template->structure);


// ✅ REDIRIGIR al Paso 3
header('Location: /addendas/frontend/wizard_step3.php?template_id=' . urlencode($template->id));
exit;