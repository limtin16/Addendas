<?php

ob_start();
require_once dirname(__DIR__) . '/config.php';

require_once BACKEND_ROOT . '/src/DTO/Template.php';
require_once BACKEND_ROOT . '/src/DTO/CfdiMap.php';


require_once BACKEND_ROOT . '/src/Services/TemplateService.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';
require_once BACKEND_ROOT . '/src/Services/CFDIParserService.php';
require_once BACKEND_ROOT . '/src/Services/CfdiValueResolver.php';
require_once BACKEND_ROOT . '/src/Services/AddendaAutofillService.php';
require_once BACKEND_ROOT . '/src/Services/CfdiAddendaInserter.php';



use App\Services\AddendaXmlBuilder;
use App\Services\AddendaAutofillService;
use App\Services\CfdiValueResolver;
use App\Services\CfdiAddendaInserter;
use App\Services\TemplateService;


// ✅ Validar POST
$templateId = $_POST['template_id'] ?? '';
$file       = $_FILES['cfdi'] ?? null;

if ($templateId === '' || !$file) {
    http_response_code(400);
    exit('Faltan datos');
}
try {
    // Leer CFDI subido
    $cfdiXml = file_get_contents($_FILES['cfdi']['tmp_name']);
    if ($cfdiXml === false) {
        throw new Exception('No se pudo leer el CFDI subido');
    }

    // Cargar template
    $templateService = new TemplateService();
    $template = $templateService->get($templateId);

    if (!$template) {
        throw new Exception('Template no encontrado');
    }

    // Generar Addenda
	$structure = $template->structure ?? [];

	// 1. Construir Addenda
	$builder = new AddendaXmlBuilder();
	$addendaXml = $builder->build($structure);

	// 2. Autofill (USA EL MISMO $structure NORMALIZADO)
	$autofill = new AddendaAutofillService();
	$addendaXml = $autofill->fill(
		$addendaXml,
		$cfdiXml,
		$structure
	);
	
	if (strpos($addendaXml, '{{') !== false) {

    preg_match_all('/\{\{([^\}]+)\}\}/', $addendaXml, $matches);

    throw new Exception(
        'Autofill falló. Placeholders restantes: ' .
        implode(', ', array_unique($matches[1]))
    );
}
	
	// DEBUG TEMPORAL
	if (strpos($addendaXml, '{{') !== false) {
		throw new Exception('Autofill no reemplazó todos los placeholders');
	}


    if (!$addendaXml) {
        throw new Exception('No se pudo generar la Addenda XML');
    }

	// 3. Insertar Addenda en CFDI
	$inserter = new CfdiAddendaInserter();
	$resultCfdi = $inserter->insert($cfdiXml, $addendaXml);

    if (!$resultCfdi) {
        throw new Exception('No se pudo insertar la Addenda en el CFDI');
    }

    // ✅ ÉXITO: devolver CFDI final
	ob_clean();
    header('Content-Type: application/xml; charset=UTF-8');
    echo $resultCfdi;
    exit;

} catch (Throwable $e) {
    // Mostrar error exacto (solo para debug)
	ob_clean();
    http_response_code(500);
    echo 'ERROR: ' . $e->getMessage();
    exit;
}