<?php
session_start();

header('Content-Type: application/json');

require_once dirname(__DIR__) . '../db.php';

$userId = $_SESSION['user_id'] ?? null;

// ✅ XML obligatorio
$xml = $_POST['xml'] ?? null;

if (!$xml) {
    echo json_encode(['error' => 'No XML']);
    exit;
}

// ✅ generar filename
$filename = 'cfdi_' . time() . '.xml';

// ✅ guardar archivo físico
$path = dirname(__DIR__) . '../src/storage/cfdi_generated/' . $filename;
file_put_contents($path, $xml);

// ✅ token (para recuperación futura)
$token = bin2hex(random_bytes(8));

// ✅ guardar en BD (👀 user_id puede ser NULL)
$stmt = $conn->prepare("
    INSERT INTO generated_cfdis (user_id, filename, xml, token, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

$stmt->bind_param("isss", $userId, $filename, $xml, $token);
$stmt->execute();

$id = $stmt->insert_id;

// ✅ respuesta
echo json_encode([
    "id" => $id
]);