<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
session_start();
require_once dirname(__DIR__) . '/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    die("Faltan datos");
}

$stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];

    // ✅ redirige al sistema
    header("Location: <?= $base ?>/frontend/dashboard.php");
    exit;

} else {
    echo "Credenciales incorrectas";
}