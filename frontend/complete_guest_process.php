<?php
session_start();

require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/db.php';

// ✅ validar guest
if (empty($_SESSION['guest_paid']) || empty($_SESSION['guest_paid_id'])) {
    echo json_encode(["success" => false]);
    exit;
}

// ✅ leer input
$data = json_decode(file_get_contents("php://input"), true);

$cfdiId = intval($data['cfdi_id'] ?? 0);

// ✅ actualizar payment
$stmt = $conn->prepare("
    UPDATE payments
    SET guest_cfdi_id = ?
    WHERE id = ?
");

$stmt->bind_param("ii", $cfdiId, $_SESSION['guest_paid_id']);
$stmt->execute();

// ✅ destruir sesión (clave)
session_unset();
session_destroy();

echo json_encode(["success" => true]);