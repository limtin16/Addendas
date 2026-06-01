<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

session_start();

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
$email = $_SESSION['email'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

// ✅ leer datos del frontend
$input = json_decode(file_get_contents("php://input"), true);

$credits = isset($input['credits']) ? (int)$input['credits'] : 1;

// ✅ precios dinámicos (puedes expandir esto)
$prices = [
    1   => 10000,
    10  => 85000,
    20  => 150000,
    50  => 325000,
    100 => 550000,
    200 => 1000000,
    300 => 1350000,
    500 => 2000000
];

if (!isset($prices[$credits])) {
    http_response_code(400);
    echo json_encode(["error" => "Plan inválido"]);
    exit;
}

$baseAmount = $prices[$credits];
$amount = round($baseAmount * 1.16); // ✅ agrega 16%

// ✅ API KEY
$apiKey = "key_tAsPP4OcAeVh6YcHl1ltOyR"; // ⚠️ privada
$url = "https://api.conekta.io/orders";

// ✅ request
$data = [
    "currency" => "MXN",

    "customer_info" => [
        "name" => "Usuario",
        "email" => $email
    ],

    "line_items" => [[
        "name" => "$credits Addenda(s)",
        "unit_price" => $amount,
        "quantity" => 1
    ]],

    // 💣 IMPORTANTE → metadata
    "metadata" => [
        "user_id" => $userId,
        "credits" => $credits
    ],

    "checkout" => [
        "type" => "Integration",
        "allowed_payment_methods" => ["card", "cash", "bank_transfer"],

        // ✅ AQUÍ ESTÁ LA CLAVE
        "success_url" => "https://www.addendafacil.com/frontend/dashboard.php?paid=1",
        "failure_url" => "https://www.addendafacil.com/frontend/buy_credits.php?failed=1"
    ]
];

// ✅ CURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => $apiKey . ":",
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
    "Content-Type: application/json",
    "Accept: application/vnd.conekta-v2.0.0+json"
]
]);

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode([
        "error" => curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$result = json_decode($response, true);

curl_close($ch);

// ✅ validar respuesta
if (!isset($result["checkout"]["id"])) {

    http_response_code(500);
    echo json_encode([
        "error" => "Respuesta inválida de Conekta",
        "debug" => $result
    ]);
    exit;
}

// ✅ respuesta final
echo json_encode([
    "checkoutId" => $result["checkout"]["id"]
]);