<?php

require_once dirname(__DIR__) . '/config.php';
require_once BACKEND_ROOT . '/src/Services/CFDIParserService.php';

use App\Services\CFDIParserService;

header('Content-Type: application/json');

// ====================================================
// CFDI REAL (ajusta esta ruta si es necesario)
// ====================================================
$cfdiPath = BACKEND_ROOT . '/src/storage/cfdi/ejemplo.xml';

if (!file_exists($cfdiPath)) {
    echo json_encode([
        'error' => 'CFDI de ejemplo no encontrado',
        'path'  => $cfdiPath
    ]);
    exit;
}

$xml = file_get_contents($cfdiPath);

$parser = new CFDIParserService();
$cfdi = $parser->parse($xml);

$fields = [];

// ====================================================
// COMPROBANTE (ROOT)
// ====================================================
if (!empty($cfdi->comprobante) && is_array($cfdi->comprobante)) {
    foreach ($cfdi->comprobante as $key => $value) {

        if ($value === null || is_array($value)) {
            continue;
        }

        $fields[] = [
            'value' => strtolower($key),
            'label' => 'Comprobante.' . prettyLabel($key),
            'scope' => 'root'
        ];
    }
}

// ====================================================
// CONCEPTOS
// ====================================================
if (!empty($cfdi->conceptos) && isset($cfdi->conceptos[0])) {
    foreach ($cfdi->conceptos[0] as $key => $value) {

        if ($value === null || is_array($value)) {
            continue;
        }

        $fields[] = [
            'value' => strtolower($key),
            'label' => 'Conceptos.' . prettyLabel($key),
            'scope' => 'concept'
        ];
    }
}

// ====================================================
// RESPUESTA
// ====================================================
echo json_encode([
    'fields' => $fields
]);

// ====================================================
// UTILIDAD PARA LABELS HUMANOS
// ====================================================
function prettyLabel($key)
{
    $map = [
        // --- Comprobante ---
        'folio'             => 'Folio',
        'serie'             => 'Serie',
        'fecha'             => 'Fecha',
        'moneda'            => 'Moneda',
        'tipodecomprobante' => 'Tipo de Comprobante',
        'subtotal'          => 'SubTotal',
        'total'             => 'Total',
        'lugarexpedicion'   => 'Lugar de Expedición',
        'exportacion'       => 'Exportación',

        // --- Conceptos ---
        'cantidad'          => 'Cantidad',
        'valorunitario'     => 'Valor Unitario',
        'claveunidad'       => 'Clave Unidad',
        'noidentificacion'  => 'No. Identificación',
        'descripcion'       => 'Descripción',
        'importe'           => 'Importe',
    ];

    $key = strtolower($key);

    return isset($map[$key]) ? $map[$key] : ucfirst($key);
}