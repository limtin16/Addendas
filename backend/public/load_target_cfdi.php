<?php
session_start();

/* =======================================================
   Validar archivo
   ======================================================= */

if (
    !isset($_FILES['target_cfdi']) ||
    $_FILES['target_cfdi']['error'] !== UPLOAD_ERR_OK
) {
    http_response_code(400);
    echo json_encode([
        'error' => 'No se recibió el CFDI destino'
    ]);
    exit;
}

$xml = file_get_contents($_FILES['target_cfdi']['tmp_name']);

if (!$xml || trim($xml) === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'El CFDI destino está vacío'
    ]);
    exit;
}

/* =======================================================
   Guardar CFDI DESTINO en sesión
   ======================================================= */

$_SESSION['target_cfdi_xml'] = $xml;

echo json_encode([
    'ok' => true
]);