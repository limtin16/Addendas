<?php
require_once dirname(__DIR__) . '/db.php';

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';

if (!$token || !$password) {
    die("Datos inválidos");
}

// ✅ buscar usuario
$stmt = $conn->prepare("
    SELECT id, reset_expires
    FROM users
    WHERE reset_token = ?
");

$stmt->bind_param("s", $token);
$stmt->execute();

$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    die("Token inválido");
}

// ✅ verificar expiración
if (strtotime($user['reset_expires']) < time()) {
    die("Token expirado");
}

// ✅ hash de password
$hash = password_hash($password, PASSWORD_BCRYPT);

// ✅ actualizar
$stmt = $conn->prepare("
    UPDATE users
    SET password = ?, reset_token = NULL, reset_expires = NULL
    WHERE id = ?
");
$stmt->bind_param("si", $hash, $user['id']);
$stmt->execute();

echo "✅ Contraseña actualizada correctamente";