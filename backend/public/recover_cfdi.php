<?php
require_once dirname(__DIR__) . '/db.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token requerido");
}

// Buscar CFDI
$stmt = $conn->prepare("
    SELECT filename FROM generated_cfdis WHERE token = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("CFDI no encontrado");
}

$path = dirname(__DIR__) . "/src/storage/cfdi_generated/" . $data['filename'];

if (!file_exists($path)) {
    die("Archivo no existe");
}

// Descargar
header('Content-Type: application/xml');
header('Content-Disposition: attachment; filename="cfdi_recuperado.xml"');

readfile($path);
exit;