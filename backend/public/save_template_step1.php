<?php

define('BASE_PATH', dirname(__DIR__));

require_once dirname(__DIR__) . '/config.php';
require_once BASE_PATH . '/src/DTO/Template.php';
require_once BASE_PATH . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

// Validación básica
$name = trim($_POST['name'] ?? '');
$location = $_POST['location'] ?? '';

if ($name === '' || !in_array($location, ['ADDENDA', 'COMPLEMENTO'])) {
    die('❌ Datos inválidos. Regresa e intenta de nuevo.');
}

$service = new TemplateService();

// Estructura inicial VACÍA (se completará en el Paso 2)
$structure = [
    'root' => [
        'name' => null,
        'prefix' => null,
        'namespace' => null,
        'children' => []
    ]
];

// ✅ Crear template
$template = $service->save($name, $location, $structure);

header('Location: /addendas/frontend/wizard_step2.php?template_id=' . urlencode($template->id));
exit;