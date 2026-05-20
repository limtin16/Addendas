<?php
session_start();

// 🔁 copia TODO tu código de generate_final_cfdi.php
// PERO elimina headers de descarga

if (!isset($_SESSION['original_cfdi_xml'])) {
    die('Error');
}

if (!isset($_POST['addenda_xml'])) {
    die('Error');
}

$originalCfdi = $_SESSION['original_cfdi_xml'];
$newAddendaXml = trim($_POST['addenda_xml']);

// 👉 usa EXACTAMENTE tu mismo código de inserción aquí

// RESULTADO FINAL:
$finalCfdi = "..."; // (tu lógica actual)


// ✅ IMPORTANTE: NO headers descarga
header('Content-Type: application/json');

echo json_encode([
    'xml' => $finalCfdi
]);