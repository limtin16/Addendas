<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
require_once dirname(__DIR__) . '/db.php';
$email = $_POST['email'] ?? '';

if (!$email) {
    die("Correo requerido");
}

// ✅ validar existencia
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    die("❌ El correo no existe");
}

// ✅ generar token
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// ✅ guardar token
$stmt = $conn->prepare("
    UPDATE users SET reset_token = ?, reset_expires = ?
    WHERE id = ?
");
$stmt->bind_param("ssi", $token, $expires, $user['id']);
$stmt->execute();

// ✅ link
$link = "http://localhost<?= $base ?>/frontend/reset_password.php?token=$token";

// ✅ enviar correo (simple)
$subject = "Recuperación de contraseña";
$message = "Haz clic aquí para cambiar tu contraseña:\n$link";

mail($email, $subject, $message);

// ✅ respuesta
echo "✅ Se envió un enlace de recuperación (si el correo existe)";