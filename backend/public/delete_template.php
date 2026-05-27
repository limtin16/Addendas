<?php
session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /frontend/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$templateId = $_POST['id'] ?? null;

if (!$templateId) {
    die("❌ ID inválido");
}

// ✅ borrar SOLO si pertenece al usuario
$stmt = $conn->prepare("
    DELETE FROM templates 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $templateId, $userId);
$stmt->execute();

header("Location: /frontend/templates_list.php");
exit;