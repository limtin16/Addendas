<?php
session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    die("No autorizado");
}

$id = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// ✅ validar que el CFDI pertenece al usuario
$stmt = $conn->prepare("
    SELECT filename FROM generated_cfdis 
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param("ii", $id, $userId);
$stmt->execute();

$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    die("CFDI no encontrado");
}

$path = dirname(__DIR__) . "/src/storage/cfdi_generated/" . $res['filename'];

if (!file_exists($path)) {
    die("Archivo no existe");
}

// ✅ descarga
header('Content-Type: application/xml');
header('Content-Disposition: attachment; filename="' . $res['filename'] . '"');

readfile($path);
exit;