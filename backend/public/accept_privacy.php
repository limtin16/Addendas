<?php
session_start();
require_once dirname(__DIR__) . '../db.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /addendas/frontend/login.php");
    exit;
}

// obtener datos técnicos
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// obtener versión activa
$stmt = $conn->prepare("
    SELECT version FROM privacy_policy WHERE active = 1 LIMIT 1
");
$stmt->execute();
$policy = $stmt->get_result()->fetch_assoc();

$version = $policy['version'];

$stmt = $conn->prepare("
    INSERT INTO privacy_acceptance 
    (user_id, accepted_at, ip_address, user_agent, version)
    VALUES (?, NOW(), ?, ?, ?)
");

$stmt->bind_param("isss", $userId, $ip, $userAgent, $version);
$stmt->execute();



// redirigir al sistema
header("Location: /addendas/frontend/dashboard.php");
exit;