<?php

require_once dirname(__DIR__) . '/config.php';

require_once BACKEND_ROOT . '/src/DTO/Template.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';
require_once BACKEND_ROOT . '/src/Services/AddendaAutofillService.php';
require_once BACKEND_ROOT . '/src/Services/CfdiAddendaInserter.php';

use App\Services\TemplateService;
use App\Services\AddendaXmlBuilder;
use App\Services\AddendaAutofillService;
use App\Services\CfdiAddendaInserter;

// ----------------------
// 1. Template
// ----------------------
$templateId = $_GET['template_id'] ?? null;
if (!$templateId) {
    die('template_id requerido');
}

$templateService = new TemplateService();
$template = $templateService->get($templateId);

if (!$template) {
    die('Template no encontrado');
}

// ----------------------
// 2. Construir Addenda base
// ----------------------
$builder = new AddendaXmlBuilder();
$addendaXml = $builder->build($template->structure);


/**
 * 3. Leer CFDI real
 */
$cfdiPath = BACKEND_ROOT . '/src/Storage/cfdi/ejemplo.xml';

if (!file_exists($cfdiPath)) {
    die('CFDI no encontrado en: ' . $cfdiPath);
}

$cfdiXml = file_get_contents($cfdiPath);


// ----------------------
// 4. AUTOFILL
// ----------------------
$autofill = new AddendaAutofillService();

$filledAddenda = $autofill->fill(
    $addendaXml,
    $cfdiXml,
    $template->structure
);

// ----------------------
// 5. Insertar Addenda en CFDI
// ----------------------
$inserter = new CfdiAddendaInserter();
$finalCfdi = $inserter->insert($cfdiXml, $filledAddenda);

// ----------------------
// 6. Salida
// ----------------------
header('Content-Type: application/xml; charset=UTF-8');
echo $finalCfdi;