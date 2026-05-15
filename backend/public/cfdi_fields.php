<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

// ====================================================
// CFDI REAL (ajusta esta ruta si es necesario)
// ====================================================
session_start();

if (!isset($_SESSION['target_cfdi_xml'])) {
    echo json_encode([
        'error' => 'No CFDI loaded'
    ]);
    exit;
}

$xml = $_SESSION['target_cfdi_xml'];

$dom = new DOMDocument();
$dom->loadXML($xml);

$fields = [];

// ====================================================
// Recorrer TODO el CFDI (DOM completo)
// ====================================================
function walkNode(DOMElement $el, string $path, array &$out)
{
    foreach ($el->attributes as $attr) {
        if ($attr->prefix === 'xmlns') continue;

        $out[] = [
            'value' => strtolower($attr->name),
            'label' => $path . ' → @' . $attr->name,
            'scope' => 'all'
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

// iniciar desde el root
walkNode(
    $dom->documentElement,
    $dom->documentElement->nodeName,
    $fields
);

echo json_encode([
    'fields' => $fields
]);
