<?php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/src/DTO/Template.php';
require_once BASE_PATH . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

$service = new TemplateService();

// Simulación de estructura (luego vendrá del HTML)
$structure = [
    'root' => 'Factura',
    'location' => 'ADDENDA',
    'fields' => [
        [
            'name' => 'Folio',
            'type' => 'string'
        ]
    ],
    'groups' => []
];

$template = $service->save(
    'Plantilla de prueba',
    'ADDENDA',
    $structure
);

header('Content-Type: application/json; charset=utf-8');

echo json_encode(
    $template->toArray(),
);
?>