<?php

require_once dirname(__use App\Services\TemplateService;require_once dirname(__DIR__) . '/config.php';
use App\Services\AddendaXmlBuilder;

$templateId = $_GET['template_id'] ?? null;
if (!$templateId) {
    http_response_code(400);
    exit('template_id requerido');
}

$service = new TemplateService();
$template = $service->get($templateId);
if (!$template) {
    http_response_code(404);
    exit('Template no encontrado');
}

$builder = new AddendaXmlBuilder();
$xml = $builder->build($template->structure);

// Preview estructural con placeholders visibles
$doc = new DOMDocument();
$doc->loadXML($xml);

foreach ($template->structure['root']['children'] as $node) {
    if ($node['type'] !== 'field') continue;

    $value = isset($node['value'])
        ? $node['value']
        : (isset($node['source']) ? '{{'.$node['source'].'}}' : null);

    if ($value) {
        $doc->documentElement->setAttribute($node['name'], $value);
    }
}

echo json_encode([
    'structurePreview' => $doc->saveXML($doc->documentElement),
    'simulatedPreview' => str_replace('{{cfdi.moneda}}', 'MXN',
                             $doc->saveXML($doc->documentElement))
]);

require_once BACKEND_ROOT . '/src/Services/TemplateService.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

