<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/helpers/paypal.php';

session_start();

$redirect = $_GET['redirect'] ?? BASE_URL . "/frontend/select_mode.php";

// ✅ cálculo
$price = 115;
$iva = $price * 0.16;
$total = round($price + $iva, 2);

// ✅ metadata
$custom = json_encode([
    "type" => "guest_addenda",
    "redirect" => $redirect
]);

$order = createPayPalOrder([
    "amount" => $total,
    "currency" => "MXN",
    "custom_id" => $custom
]);

if (!$order) {
    http_response_code(500);
    echo json_encode(["error" => "error creando orden"]);
    exit;
}

echo json_encode(["id" => $order->id]);