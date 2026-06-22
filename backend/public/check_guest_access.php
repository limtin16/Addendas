<?php
session_start();

// ✅ includes
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/db.php';

$redirect = $_GET['redirect'] ?? BASE_URL . "/frontend/select_mode.php";

/**
 * ✅ PASO 1: buscar pago REAL en DB
 */
$stmt = $conn->prepare("
    SELECT id, external_order_id 
    FROM payments 
    WHERE user_id = 4 AND provider = 'paypal'
    ORDER BY created_at DESC 
    LIMIT 1
");

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // ❌ No hay pago
    session_destroy();

    header("Location: " . BASE_URL . "/frontend/guest_checkout.php?redirect=" . urlencode($redirect));
    exit;
}

$payment = $result->fetch_assoc();

/**
 * ✅ PASO 3: marcar sesión (AQUÍ sucede ahora)
 */
$_SESSION['guest_paid'] = true;
$_SESSION['guest_paid_id'] = $payment['id'];

/**
 * ✅ PASO 4: redirigir al flujo real
 */
header("Location: " . $redirect);
exit;