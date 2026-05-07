<?php
session_start();

require_once dirname(__DIR__) . '/config.php';
require_once BACKEND_ROOT . '/src/Services/XsdAddendaParserService.php';

use App\Services\XsdAddendaParserService;

if (
    !isset($_FILES['addenda_xsd']) ||
    $_FILES['addenda_xsd']['error'] !== UPLOAD_ERR_OK
) {
    die('Error al subir XSD');
}

$xsdContent = file_get_contents($_FILES['addenda_xsd']['tmp_name']);
if (!$xsdContent) {
    die('XSD vacío');
}

$parser = new XsdAddendaParserService();
$addendaXml = $parser->parse($xsdContent);

// Extraer namespace DEL XSD (obligatorio por elementFormDefault=qualified)
if (!preg_match('/xmlns="([^"]+)"/', $addendaXml, $m)) {
    die('El XSD no define namespace por defecto');
}

$_SESSION['addenda_instance'] = [
    'origin'        => 'xsd',
    'xsd_namespace' => $m[1],
    'structure'     => $parser->getStructure()
];

header('Location: /addendas/frontend/render_instance_form.php');
exit;