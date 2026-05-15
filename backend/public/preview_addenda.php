<?php
session_start();

require_once dirname(__DIR__) . '/config.php';
require_once BACKEND_ROOT . '/src/Services/CFDIParserService.php';
require_once BACKEND_ROOT . '/src/Services/CfdiValueResolver.php';

use App\Services\CFDIParserService;
use App\Services\CfdiValueResolver;

// ===============================
// ✅ VALIDACIÓN SEGURA
// ===============================
if (
    !isset($_SESSION['addenda_instance']) ||
    !isset($_SESSION['addenda_instance']['addenda_xml_template'])
) {
    http_response_code(400);
    echo '❌ No hay template de addenda disponible en sesión';
    exit;
}

$templateXml = $_SESSION['addenda_instance']['addenda_xml_template'];

// ===============================
// ✅ INPUT DEL FORM
// ===============================
$raw = file_get_contents('php://input');

$input = json_decode($raw, true);

if (!is_array($input)) {
    $input = [];
}

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
    $currentPath = $path === ''
        ? $element->nodeName
        : $path . '.' . $element->nodeName;

    // ===============================
    // ✅ ATRIBUTOS
    // ===============================
    if ($element->hasAttributes()) {

        foreach ($element->attributes as $attr) {

            $key = $currentPath . '.@' . $attr->name;

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

        if ($child instanceof DOMElement) {
            applyValues($child, $inputValues, $resolver, $currentPath);
        }
    }
}

// ===============================
// ✅ APLICAR VALORES AL XML
// ===============================
applyValues($doc->documentElement, $input, $resolver);

// ===============================
// ✅ SALIDA (SOLO ADDENDA)
// ===============================
$output = $doc->saveXML($doc->documentElement);

// ✅ limpieza de espacios
$output = trim($output);

echo $output;
exit;