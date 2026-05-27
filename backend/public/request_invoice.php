<?php
session_start();
require_once dirname(__DIR__) . '../db.php';

$userId = $_SESSION['user_id'] ?? null;
$purchaseId = $_POST['purchase_id'] ?? null;

if (!$purchaseId) {
    die("Compra inválida");
}

// ✅ guardar solicitud
$stmt = $conn->prepare("
INSERT INTO invoice_requests (user_id, purchase_id)
VALUES (?, ?)
");
$stmt->bind_param("ii", $userId, $purchaseId);
$stmt->execute();

// ✅ datos del cliente
$stmt = $conn->prepare("SELECT * FROM billing_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// ✅ correo
$to = "tu_correo@gmail.com";
$subject = "🧾 Nueva solicitud de factura";

$message = "
Solicitud de factura:

Compra ID: $purchaseId

RFC: {$data['rfc']}
Nombre: {$data['name']}
CP: {$data['postal_code']}
Régimen: {$data['regime']}
Uso CFDI: {$data['cfdi_use']}
Email: {$data['email']}

Fecha: ".date('Y-m-d H:i:s');

mail($to, $subject, $message);

// ✅ respuesta elegante
header("Location: /frontend/billing.php?success=1");
exit;