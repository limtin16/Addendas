<?php

define('BASE_PATH', dirname(__DIR__));

require_once dirname(__DIR__) . '/config.php';
require_once BASE_PATH . '/src/DTO/Template.php';
require_once BASE_PATH . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

// Leer datos
$templateId   = $_POST['template_id'] ?? '';
$groupName    = trim($_POST['group_name'] ?? '');
$itemName     = trim($_POST['item_name'] ?? '');
$source       = trim($_POST['source'] ?? '');

$fieldName    = trim($_POST['field_name'] ?? '');
$representation = $_POST['representation'] ?? 'attribute';

// Validar
if ($templateId === '' || $groupName === '' || $itemName === '' || $fieldName === '') {
    die('❌ Datos inválidos.');
}

$service = new TemplateService();
$template = $service->get($templateId);

if (!$template) {
    die('❌ Template no encontrado.');
}

// ✅ CONSTRUIR GRUPO (sin tocar root)
$group = [
    'type'       => 'group',
    'name'       => $groupName,
    'itemName'   => $itemName,
    'repeatable' => true,
    'children'   => [
        [
            'type' => 'field',
            'name' => $fieldName,
            'representation' => $representation
        ]
    ]
];

if ($source !== '') {
    $group['source'] = $source;
}

// ✅ AGREGAR AL ROOT EXISTENTE
$template->structure['root']['children'][] = $group;

// ✅ GUARDAR SIN RECREAR ROOT
$service->update($templateId, $template->structure);

// ✅ Fin del wizard (luego haremos pantalla final)
header('Location: /addendas/frontend/wizard_done.php?template_id=' . urlencode($templateId));
exit;