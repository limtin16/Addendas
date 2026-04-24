<?php

// 1️⃣ Apagar errores en salida (crítico para XML)
ini_set('display_errors', '0');
error_reporting(0);

// 2️⃣ Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once dirname(__DIR__) . '/config.php';
require_once BACKEND_ROOT . '/src/DTO/Template.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\TemplateService;
use App\Services\AddendaXmlBuilder;

$templateId = $_GET['id'] ?? '';

if ($templateId === '') {
    http_response_code(400);
    exit;
}

$templateService = new TemplateService();
$template = $templateService->get($templateId);

if (!$template) {
    http_response_code(404);
    exit;
}

$builder = new AddendaXmlBuilder();
$xml = $builder->build($template->structure);

// 3️⃣ Limpiar otra vez por seguridad
ob_clean();

// 4️⃣ Enviar SOLO XML
header('Content-Type: application/xml; charset=UTF-8');
header('Content-Length: ' . strlen($xml));

echo $xml;
exit;