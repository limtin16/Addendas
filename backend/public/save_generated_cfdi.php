<?php
session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    die("No autorizado");
}

$userId = $_SESSION['user_id'];
$xml = $_POST['xml'] ?? null;

if (!$xml) {
    die("No XML");
}

/* ✅ generar filename */
$filename = 'cfdi_' . time() . '.xml';

/* ✅ guardar archivo físico */
$path = dirname(__DIR__) . '/src/storage/cfdi_generated/' . $filename;
file_put_contents($path, $xml);

/* ✅ generar token */
$token = bin2hex(random_bytes(8));

/* ✅ guardar en BD */
$stmt = $conn->prepare("
    INSERT INTO generated_cfdis (user_id, filename, xml, token, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

$stmt->bind_param("isss", $userId, $filename, $xml, $token);
$stmt->execute();

$id = $stmt->insert_id;

/* ✅ devolver ID */
echo json_encode([
    "id" => $id
]);