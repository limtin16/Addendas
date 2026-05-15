<?php

require_once dirname(__DIR__) . '/config.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\TemplateService;
use App\Services\AddendaXmlBuilder;

header('Content-Type: application/json');

$templateId = $_GET['template_id'] ?? null;
if (!$templateId) {
    http_response_code(400);
    echo json_encode(['error' => 'template_id requerido']);
    exit;
}

$service = new TemplateService();
$template = $service->get($templateId);
if (!$template) {
    http_response_code(404);
    echo json_encode(['error' => 'Template no encontrado']);
    exit;
}

$builder = new AddendaXmlBuilder();
$xmlBase = $builder->build($template->structure);
if (!$xmlBase) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo generar XML base']);
    exit;
}

// DOMs
$docStructure = new DOMDocument('1.0', 'UTF-8');
$docStructure->loadXML($xmlBase);

$docSimulated = new DOMDocument('1.0', 'UTF-8');
$docSimulated->loadXML($xmlBase);

$children = $template->structure['root']['children'];

/* =====================================================
   ROOT FIELDS
   ===================================================== */
foreach ($children as $node) {
    if ($node['type'] !== 'field') continue;
        $placeholder = null;
        $simulated = null;

        if (isset($node['value'])) {
            $placeholder = $node['value'];
            $simulated = $node['value'];
        }
        elseif (isset($node['source'])) {
            $placeholder = '{{' . $node['source'] . '}}';
            $simulated = 'VALOR';
        }
        elseif (isset($node['calculation'])) {
            $placeholder = '{{calc:' . $node['calculation'] . '}}';
            $simulated = '123.45';
        }


    if ($placeholder !== null) {
        $docStructure->documentElement->setAttribute($node['name'], $placeholder);
        $docSimulated->documentElement->setAttribute($node['name'], $simulated);
    }
}

/* =====================================================
   GROUPS
   ===================================================== */
foreach ($children as $node) {
    if ($node['type'] !== 'group') continue;

    $groupsS = $docStructure->getElementsByTagName($node['name']);
    $groupsF = $docSimulated->getElementsByTagName($node['name']);

    for ($i = 0; $i < $groupsS->length; $i++) {

        $groupS = $groupsS->item($i);
        $groupF = $groupsF->item($i);

        for ($j = 0; $j < $groupS->childNodes->length; $j++) {

            $itemS = $groupS->childNodes->item($j);
            if ($itemS->nodeType !== XML_ELEMENT_NODE) continue;

            $itemF = $groupF->childNodes->item($j);

            foreach ($node['children'] as $field) {

                $placeholder = null;
                $simulated   = null;

                if (isset($field['value'])) {
                    $placeholder = $field['value'];
                    $simulated   = $field['value'];
                }
                elseif (isset($field['source'])) {
                    $placeholder = '{{' . $field['source'] . '}}';
                    $simulated   = 'SIMULADO';
                }
                elseif (isset($field['calculation'])) {
                    $placeholder = '{{calc:' . $field['calculation'] . '}}';
                    $simulated   = '123.45';
                }

                if ($placeholder !== null) {
                    $itemS->setAttribute($field['name'], $placeholder);
                    $itemF->setAttribute($field['name'], $simulated);
                }
            }
        }
    }
}

echo json_encode([
    'structurePreview' => $docStructure->saveXML($docStructure->documentElement),
    'simulatedPreview' => $docSimulated->saveXML($docSimulated->documentElement)
]);



========================================
Archivo: C:\xampp\htdocs\addendas\backend\public\preview_addenda_live.php
========================================
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
