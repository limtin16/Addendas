<?php
// ✅ respuesta visual
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles.css">

    <!-- ⏱ Redirección automática -->
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

            <!-- ✅ BOTÓN CORRECTO -->
            <a href="<?= BASE_URL ?>/frontend/login.php" class="btn blue full">
                Ir ahora al login
            </a>

        <?php else: ?>

            <h2>❌ Error</h2>

            <p class="description">
                Ocurrió un problema al enviar el correo. Intenta nuevamente.
            </p>

            <!-- ✅ BOTÓN CORRECTO -->
            <a href="<?= BASE_URL ?>/frontend/forgot_password.php" class="btn gray full">
                Intentar de nuevo
            </a>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
<?php
exit;