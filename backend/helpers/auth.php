<?php

function requireAuthAndPrivacy($conn) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/frontend/login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT id FROM privacy_acceptance WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    if (!$stmt->get_result()->fetch_assoc()) {
        header("Location: " . BASE_URL . "/frontend/privacy.php");
        exit;
    }

    return $userId;
}
?>