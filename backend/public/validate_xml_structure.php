<?php
header('Content-Type: application/json');

if (!isset($_FILES['xml'])) {
    echo json_encode(['error' => 'No se envió XML']);
    exit;
}

libxml_use_internal_errors(true);

$xml = file_get_contents($_FILES['xml']['tmp_name']);

$dom = new DOMDocument();

if (!$dom->loadXML($xml, LIBXML_NONET)) {

    $errors = libxml_get_errors();
    libxml_clear_errors();

    echo json_encode([
        'error' => trim($errors[0]->message)
    ]);
    exit;
}

/* ============================================
   ✅ VALIDAR QUE EXISTA ADDENDA
============================================ */

$xpath = new DOMXPath($dom);

// registrar namespace CFDI
$xpath->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');

// buscar Addenda (CFDI)
$addenda = $xpath->query('//cfdi:Addenda')->item(0);

// fallback: por si es XML suelto de addenda
if (!$addenda) {
    $addenda = $xpath->query('//*[local-name()="Addenda"]')->item(0);
}

if (!$addenda) {
    echo json_encode([
        'error' => 'El XML no contiene nodo Addenda'
    ]);
    exit;
}

/* ============================================
   ✅ TODO OK
============================================ */

echo json_encode([
    'valid' => true,
    'message' => 'XML correcto y contiene Addenda'
]);