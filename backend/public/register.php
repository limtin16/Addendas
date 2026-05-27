<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
session_start();
require_once dirname(__DIR__) . '/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    die("Faltan datos");
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $hashedPassword);

if ($stmt->execute()) {
    header("Location: <?= $base ?>/frontend/login.php");
    exit;
} else {
    echo "Error: email ya existe";
}