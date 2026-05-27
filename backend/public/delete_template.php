<?php
$path = "";
$depth = substr_count(__DIR__, DIRECTORY_SEPARATOR) - substr_count(__DIR__, DIRECTORY_SEPARATOR) + substr_count(substr(__DIR__, strpos(__DIR__, 'addendas')), DIRECTORY_SEPARATOR);
for ($i = 0; $i < $depth; $i++) {
    $path .= "../";
}
$path .= "backend/config.php";
require_once $path;

session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/frontend/login.php");
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

header("Location: " . BASE_URL . "/frontend/templates_list.php");
exit;