<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
function requireAuthAndPrivacy($conn) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: <?= $base ?>/frontend/login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT id FROM privacy_acceptance WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    if (!$stmt->get_result()->fetch_assoc()) {
        header("Location: <?= $base ?>/frontend/privacy.php");
        exit;
    }

    return $userId;
}
?>