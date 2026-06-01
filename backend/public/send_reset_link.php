<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
    $path.="../";
}

$dbPath = $path . "backend/db.php";
$mailerPath = $path . "backend/helpers/mailer.php";
$path.="backend/config.php";

require_once $path;
require_once $dbPath;
require_once $mailerPath;

$email = $_POST['email'] ?? '';
$sent = false;

if ($email) {

    // ✅ buscar usuario
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($userId);
    $stmt->fetch();
    $stmt->close();

    if ($userId) {

        // ✅ generar token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // ✅ guardar token
        $stmt = $conn->prepare("
            UPDATE users 
            SET reset_token = ?, reset_expires = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssi", $token, $expires, $userId);
        $stmt->execute();
        $stmt->close();

        $link = BASE_URL . "/frontend/reset_password.php?token=" . $token;

        $body = "
            <h2>🔐 Recuperar contraseña</h2>
            <p>Haz clic en el siguiente enlace:</p>
            <p><a href='$link'>Restablecer contraseña</a></p>
            <p>Este enlace expira en 1 hora.</p>
        ";

        $sent = sendEmail($email, "Recuperación de contraseña", $body);
    } else {
        // ✅ seguridad: no revelar existencia
        $sent = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>

    <!-- ✅ CSS CORRECTO -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles.css">

    <!-- ✅ redirect -->
    <meta http-equiv="refresh" content="4;url=<?= BASE_URL ?>/frontend/login.php">
</head>
<body>

<div class="simple-page">

    <div class="simple-box">

        <?php if ($sent): ?>

            <h2>✅ Correo enviado</h2>

            <p class="description">
                Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.
            </p>

            <p style="font-size:13px; color:#666;">
                Serás redirigido al login en unos segundos...
            </p>

            <a href="<?= BASE_URL ?>/frontend/login.php" class="btn blue full">
                Ir ahora al login
            </a>

        <?php else: ?>

            <h2>❌ Error</h2>

            <p class="description">
                Ocurrió un problema al enviar el correo. Intenta nuevamente.
            </p>

            <a href="<?= BASE_URL ?>/frontend/forgot_password.php" class="btn gray full">
                Intentar de nuevo
            </a>

        <?php endif; ?>

    </div>

</div>

</body>
</html>