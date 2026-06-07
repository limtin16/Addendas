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
$templateServicePath = $path . "backend/src/Services/TemplateService.php";
$path.="backend/config.php";
require_once $path;
require_once $templateServicePath;


require_once BACKEND_ROOT . '/src/Services/CFDIParserService.php';
require_once BACKEND_ROOT . '/src/Services/CfdiValueResolver.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\CFDIParserService;
use App\Services\CfdiValueResolver;
use App\Services\TemplateService;

$service = new TemplateService();

ob_clean();
header('Content-Type: text/plain');
$data = json_decode(file_get_contents("php://input"), true);
$templateId =  $data['template_id'];

$template = $service->get($templateId);

// ===============================
// ✅ VALIDACIÓN SEGURA
// ===============================
$structure = $template->structure['root']['addenda_xml_template'];
if (
    null == ($structure)
) {
    http_response_code(400);
    echo '❌ No hay template de addenda disponible en sesión';
    exit;
}

$templateXml = $template->structure['root']['addenda_xml_template'];
// ===============================
// ✅ INPUT DEL FORM
// ===============================
$raw = file_get_contents('php://input');

$input = json_decode($raw, true);

if (!is_array($input)) {
    $input = [];
}
$templateNs = $input['addenda_namespace'] ?? '';
$templateNs = trim($templateNs);

// ===============================
// ✅ CARGAR XML
// ===============================
$doc = new DOMDocument('1.0', 'UTF-8');
$doc->preserveWhiteSpace = false;
$doc->formatOutput = true;

if (!$doc->loadXML($templateXml)) {
    http_response_code(500);
    echo '❌ Error al cargar XML template';
    exit;
}

$resolver = null;

if (isset($_SESSION['target_cfdi_xml'])) {
    $parser = new CFDIParserService();
    $cfdiMap = $parser->parse($_SESSION['target_cfdi_xml']);
    $resolver = new CfdiValueResolver($cfdiMap);
}

function prettyXml($xml) {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;

    if (!$doc->loadXML($xml)) {
        return $xml;
    }

    return $doc->saveXML($doc->documentElement);
}

function normalizeCfdiPath(string $path): string
{
    // cfdi:Comprobante.@Moneda → moneda
    if (preg_match('/@([A-Za-z0-9_]+)/', $path, $m)) {
        return 'cfdi.' . strtolower($m[1]);
    }

    return $path;
}
// ===============================
// ✅ APLICAR VALORES
// ===============================
function applyValues(DOMElement $element, array $inputValues, $resolver, string $path = '')
{
    // construir path actual
    $tag = preg_replace('/^.*:/', '', $element->nodeName);
    $currentPath = $path === ''
        ? $tag
        : $path . '.' . $tag;

    // ===============================
    // ✅ ATRIBUTOS
    // ===============================
    if ($element->hasAttributes()) {

        foreach ($element->attributes as $attr) {

            if (!isset($attr->name)) continue;

            $attrName = preg_replace('/^.*:/', '', $attr->name);
            $key = $currentPath . '.' . $attrName;

            if (isset($inputValues[$key])) {

                $valueData = $inputValues[$key];

                $value = $valueData['value'] ?? '';
                $source = $valueData['source'] ?? null;

                // ✅ si hay source CFDI → usar resolver
                if ($source && $resolver) {
                    try {
                        // ✅ NORMALIZAR path tipo cfdi:Comprobante.@Moneda → cfdi.moneda
                            $normalizedSource = normalizeCfdiPath($source);

                            $resolved = $resolver->resolve($normalizedSource);

                        if ($resolved !== null && $resolved !== '') {
                            $value = $resolved;
                        }
                    } catch (\Throwable $e) {
                        // fallback: deja valor manual
                    }
                }

                // ✅ aplicar valor
                $element->setAttribute(
                    $attr->name,
                    htmlspecialchars($value)
                );
            }
        }
    }

    // ===============================
    // ✅ HIJOS
    // ===============================
        foreach ($element->childNodes as $child) {

            if (!$child instanceof DOMElement) continue;

            applyValues($child, $inputValues, $resolver, $currentPath);
        }
}

// ===============================
// ✅ APLICAR VALORES AL XML
// ===============================
applyValues($doc->documentElement, $input, $resolver);

// ===============================
// ✅ SALIDA (SOLO ADDENDA)
// ===============================
$output = trim($doc->saveXML($doc->documentElement));

$mode = $_SESSION['addenda_mode'] ?? 'manual';

if ($mode !== 'xml') {
    $output = prettyXml($output);
}

// ✅ tomar namespace definido en el template (si existe)
$templateNs = trim($templateNs);

// ✅ detectar prefijo del tag (cfdi:Addenda)
$prefix = 'cfdi'; // fallback

if (preg_match('/^<([a-zA-Z0-9_]+):Addenda/', $templateXml, $m)) {
    $prefix = $m[1];
}

// ✅ construir namespace correcto
$xmlnsAttr = '';

if ($templateNs !== '') {
    if ($prefix !== '') {
        $xmlnsAttr = 'xmlns:' . $prefix . '="' . htmlspecialchars($templateNs) . '"';
    } else {
        $xmlnsAttr = 'xmlns="' . htmlspecialchars($templateNs) . '"';
    }
}

// ✅ construir apertura
$addendaOpen = '<' . ($prefix ? $prefix . ':' : '') . 'Addenda';

if ($xmlnsAttr !== '') {
    $addendaOpen .= ' ' . $xmlnsAttr;
}

$addendaOpen .= '>';

$output = trim($output);

// ✅ detectar si ya es Addenda completa
if (preg_match('/^<([a-zA-Z0-9_]+:)?Addenda\b/i', $output)) {

    // ✅ ya viene envuelto → no volver a envolver
    echo $output;
    exit;
}

// ✅ si NO → envolver normalmente
$wrapped =
    $addendaOpen .
    $output .
    '</cfdi:Addenda>';

echo $wrapped;
exit;

echo $wrapped;
exit;