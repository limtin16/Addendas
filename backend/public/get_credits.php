<?php
session_start();
require_once dirname(__DIR__) . '../db.php';
require_once dirname(__DIR__) . '../src/Services/CreditService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'ok' => false,
        'error' => 'No autorizado'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

$creditService = new CreditService($conn);
$credits = $creditService->getAvailableCredits($userId);

echo json_encode([
    'ok' => true,
    'credits' => $credits
]);