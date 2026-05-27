<?php
session_start();
require_once dirname(__DIR__) . '/db.php';

// ✅ validar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: /addendas/frontend/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// ✅ obtener datos
$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['new_password_confirm'] ?? '';

// ✅ validar datos
if (!$current || !$new || !$confirm) {
    die("❌ Todos los campos son obligatorios");
}

// ✅ validar que coincidan nuevas contraseñas
if ($new !== $confirm) {
    die("❌ Las nuevas contraseñas no coinciden");
}

// ✅ obtener contraseña actual del usuario
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

$res = $stmt->get_result();
$user = $res->fetch_assoc();

// ✅ validar password actual
if (!$user || !password_verify($current, $user['password'])) {
    die("❌ Contraseña actual incorrecta");
}

// ✅ hashear nueva contraseña
$newHash = password_hash($new, PASSWORD_BCRYPT);

// ✅ actualizar en DB
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $newHash, $userId);
$stmt->execute();

// ✅ RESPUESTA CON MENSAJE + REDIRECT
echo "
<!DOCTYPE html>
<html>
<head>
    <title>Contraseña actualizada</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success {
            color: #28a745;
            font-size: 18px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

<div class='box'>
    <div class='success'>✅ Contraseña actualizada correctamente</div>
    <div>Redirigiendo a configuración...</div>
</div>

<script>
    setTimeout(function () {
        window.location.href = '/addendas/frontend/account_settings.php';
    }, 1500);
</script>

</body>
</html>
";
exit;