<?php
session_start();
require_once dirname(__DIR__) . '..\db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok'=>false, 'error'=>'No login']);
    exit;
}

$userId = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['ok'=>false, 'error'=>'JSON inválido']);
    exit;
}

$credits = (int)($data['credits'] ?? 0);

if ($credits <= 0) {
    echo json_encode(['ok'=>false, 'error'=>'Créditos inválidos']);
    exit;
}

$planExpirations = [
    1 => 15,
    10 => 90,
    20 => 90,
    30 => 90,
    50 => 120,
    100 => 180,
    200 => 180,
    300 => 365,
    500 => 365
];

if (!isset($planExpirations[$credits])) {
    echo json_encode(['ok'=>false, 'error'=>'Plan no válido']);
    exit;
}

$days = $planExpirations[$credits];

$now = new DateTime();
$now->modify("+{$days} days");
$expiresAt = $now->format('Y-m-d H:i:s');

$stmt = $conn->prepare("
    INSERT INTO user_credit_batches
    (user_id, credits, remaining_credits, expires_at)
    VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode([
        'ok'=>false,
        'error'=>$conn->error
    ]);
    exit;
}

$stmt->bind_param("iiis", $userId, $credits, $credits, $expiresAt);
$stmt->execute();

echo json_encode([
    'ok' => true,
    'credits_added' => $credits,
    'expires_at' => $expiresAt
]);