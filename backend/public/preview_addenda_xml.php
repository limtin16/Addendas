<?php

require_once dirname(__DIR__) . '/config.php';
require_once BACKEND_ROOT . '/src/DTO/Template.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\TemplateService;
use App\Services\AddendaXmlBuilder;

$templateId = $_GET['template_id'] ?? '';

if ($templateId === '') {
    http_response_code(400);
    exit('Template ID requerido');
}

$service = new TemplateService();
$template = $service->get($templateId);

if (!$template) {
    http_response_code(404);
    exit('Template no encontrado');
}

$builder = new AddendaXmlBuilder();

// ⚠️ Solo estructura (sin autofill)
$xml = $builder->build($template->structure);

// Devolver como texto plano para preview
header('Content-Type: application/xml; charset=utf-8');
echo $xml;