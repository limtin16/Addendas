<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

session_start();

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

// ✅ obtener email real del usuario
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultUser = $stmt->get_result()->fetch_assoc();

$email = $resultUser["email"] ?? null;

if (!$email) {
    echo json_encode(["error" => "Email no encontrado"]);
    exit;
}

// ✅ leer datos del frontend
$input = json_decode(file_get_contents("php://input"), true);
$credits = isset($input['credits']) ? (int)$input['credits'] : 1;

// ✅ precios
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

$amount = $prices[$credits];

// ✅ API KEY
$apiKey = "key_test_xxxxx"; // ⚠️ TU PRIVATE KEY

$url = "https://api.conekta.io/orders";

// ✅ request a Conekta
$data = [
    "currency" => "MXN",

    "customer_info" => [
        "name" => "Cliente",
        "email" => $email
    ],

    "line_items" => [[
        "name" => "$credits Addenda(s)",
        "unit_price" => $amount,
        "quantity" => 1
    ]],

    "metadata" => [
        "user_id" => $userId,
        "credits" => $credits
    ],

    // ✅ 💣 CLAVE: HOSTED
    "checkout" => [
        "type" => "Hosted",
        "allowed_payment_methods" => ["card", "cash", "bank_transfer"],
        "return_url" => "https://www.addendafacil.com/frontend/payment_success.php"
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
    echo json_encode(["error" => curl_error($ch)]);
    exit;
}

$result = json_decode($response, true);

curl_close($ch);

// ✅ DEBUG SI FALLA
if (!isset($result["checkout"]["url"])) {
    echo json_encode([
        "error" => "Error en Conekta",
        "debug" => $result
    ]);
    exit;
}

// ✅ RESPUESTA FINAL
echo json_encode([
    "checkoutUrl" => $result["checkout"]["url"]
]);