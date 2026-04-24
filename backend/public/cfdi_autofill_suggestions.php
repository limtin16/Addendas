<?php
session_start();

require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['target_cfdi_xml'])) {
    echo json_encode([
        'error' => 'No target CFDI loaded'
    ]);
    exit;
}

$xml = $_SESSION['target_cfdi_xml'];

$dom = new DOMDocument();
$dom->loadXML($xml);

$xpath = new DOMXPath($dom);

/* Registrar namespaces del CFDI */
foreach ($dom->documentElement->attributes as $attr) {
    if ($attr->prefix === 'xmlns') {
        $xpath->registerNamespace($attr->localName, $attr->nodeValue);
    }
}

$fields = [];

/**
 * Recorre TODOS los nodos y extrae atributos
 */
function walkNode(DOMElement $el, string $path, array &$out)
{
    foreach ($el->attributes as $attr) {
        if ($attr->prefix === 'xmlns') continue;

        $out[] = [
            'path'  => $path . '.@' . $attr->name,
            'label' => $path . ' → @' . $attr->name,
            'value' => $attr->value
        ];
    }

    foreach ($el->childNodes as $child) {
        if ($child instanceof DOMElement) {
            walkNode(
                $child,
                $path . '.' . $child->nodeName,
                $out
            );
        }
    }
}

walkNode(
    $dom->documentElement,
    $dom->documentElement->nodeName,
    $fields
);

echo json_encode([
    'fields' => $fields
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);