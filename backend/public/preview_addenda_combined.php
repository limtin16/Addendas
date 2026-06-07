<?php

session_start();

$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$path.="backend/config.php";
require_once $path;
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\TemplateService;
use App\Services\AddendaXmlBuilder;

header('Content-Type: application/json');

// ===============================
// ✅ VALIDAR template_id
// ===============================
$templateId = $_GET['template_id'] ?? null;

if (!$templateId) {
    http_response_code(400);
    echo json_encode(['error' => 'template_id requerido']);
    exit;
}

// ===============================
// ✅ OBTENER TEMPLATE
// ===============================
$service = new TemplateService();
$template = $service->get($templateId);

if (!$template) {
    http_response_code(404);
    echo json_encode(['error' => 'Template no encontrado']);
    exit;
}

// ===============================
// ✅ ASEGURAR ROOT
// ===============================
//var_dump($template->structure);
if (
    !isset($template->structure) ||
    empty($template->structure['root']['name'])
) {
    echo json_encode([
        'structurePreview' => '❌ Root inválido'
    ]);
    exit;
}

// ===============================
// ✅ GENERAR XML (SOLO BUILDER)
// ===============================
$builder = new AddendaXmlBuilder();
$xmlBase = $builder->build($template->structure);

if (!$xmlBase) {
    echo json_encode([
        'structurePreview' => '❌ Error generando XML'
    ]);
    exit;
}

// ===============================
// ✅ RESPUESTA FINAL
// ===============================
$xmlBase = trim($xmlBase);

// ===============================
// ✅ DETECTAR PREFIJO
// ===============================
$prefix = 'cfdi'; // default

if (preg_match('/^<([a-zA-Z0-9_]+):Addenda/', $xmlBase, $m)) {
    $prefix = $m[1];
}

// ===============================
// ✅ OBTENER NAMESPACE
// ===============================
$templateNs = $template->structure['root']['addenda_extra_ns'] ?? '';
$templateNs = trim($templateNs);

$xmlnsAttr = '';

if ($templateNs !== '') {
    if ($prefix !== '') {
        $xmlnsAttr = 'xmlns:' . $prefix . '="' . htmlspecialchars($templateNs) . '"';
    } else {
        $xmlnsAttr = 'xmlns="' . htmlspecialchars($templateNs) . '"';
    }
}

// ===============================
// ✅ ARMAR TAG DE APERTURA
// ===============================
$addendaOpen = '<' . ($prefix ? $prefix . ':' : '') . 'Addenda';

if ($xmlnsAttr !== '') {
    $addendaOpen .= ' ' . $xmlnsAttr;
}

$addendaOpen .= '>';

// ===============================
// ✅ VALIDAR SI YA VIENE ENVUELTO
// ===============================
if (preg_match('/^<([a-zA-Z0-9_]+:)?Addenda\b/i', $xmlBase)) {

    $finalXml = $xmlBase;

} else {

    $closingTag = '</' . ($prefix ? $prefix . ':' : '') . 'Addenda>';

    $finalXml = $addendaOpen . $xmlBase . $closingTag;
}

// ===============================
// ✅ RESPUESTA FINAL
// ===============================
// ===============================
// ✅ FORMATEAR XML
// ===============================
$doc = new DOMDocument('1.0', 'UTF-8');
$doc->preserveWhiteSpace = false;
$doc->formatOutput = true;

if ($doc->loadXML($finalXml)) {
    $prettyXml = $doc->saveXML($doc->documentElement);
} else {
    $prettyXml = $finalXml; // fallback
}

// ===============================
// ✅ RESPUESTA FINAL
// ===============================
echo json_encode([
    'structurePreview' => $prettyXml
]);

exit;