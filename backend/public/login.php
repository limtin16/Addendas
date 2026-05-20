<?php
session_start();
require_once dirname(__DIR__) . '/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    die("Faltan datos");
}

$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['user_id'] = $user['id'];

    // ✅ redirige al sistema
    header("Location: /addendas/frontend/dashboard.php");
    exit;

} else {
    echo "Credenciales incorrectas";
}