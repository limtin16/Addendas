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
$email = $_POST['email'] ?? '';
$emailConfirm = $_POST['email_confirm'] ?? '';

// ✅ validar datos
if (!$email || !$emailConfirm) {
    die("❌ Todos los campos son obligatorios");
}

// ✅ validar que coincidan
if ($email !== $emailConfirm) {
    die("❌ Los correos no coinciden");
}

// ✅ validar si correo ya existe
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$res = $stmt->get_result();

if ($res->num_rows > 0) {
    die("❌ El correo ya está en uso");
}

// ✅ actualizar email
$stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
$stmt->bind_param("si", $email, $userId);
$stmt->execute();

// ✅ RESPUESTA CON MENSAJE + REDIRECT
echo "
<!DOCTYPE html>
<html>
<head>
    <title>Correo actualizado</title>

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
    <div class='success'>✅ Correo actualizado correctamente</div>
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