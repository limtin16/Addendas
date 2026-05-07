<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

// ====================================================
// CFDI REAL (ajusta esta ruta si es necesario)
// ====================================================
$cfdiPath = BACKEND_ROOT . '/src/storage/cfdi/ejemplo.xml';

if (!file_exists($cfdiPath)) {
    echo json_encode([
        'error' => 'CFDI de ejemplo no encontrado',
        'path' => $cfdiPath
    ]);
    exit;
}

$xml = file_get_contents($cfdiPath);

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
