<?php
require_once $path;

session_start();
require_once dirname(__DIR__) . '/../db.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// ✅ query sin get_result
$stmt = $conn->prepare("
    SELECT version FROM privacy_policy WHERE active = 1 LIMIT 1
");

if (!$stmt) {
    die("Error SQL: " . $conn->error);
}

$stmt->execute();
$stmt->bind_result($version);
$stmt->fetch();

$version = $version ?: '1.0';

$stmt = $conn->prepare("
    INSERT INTO privacy_acceptance 
    (user_id, accepted_at, ip_address, user_agent, version)
    VALUES (?, NOW(), ?, ?, ?)
");

if (!$stmt) {
    die("Error SQL: " . $conn->error);
}

$stmt->bind_param("isss", $userId, $ip, $userAgent, $version);
$stmt->execute();

header("Location: " . BASE_URL . "/frontend/dashboard.php");
exit;