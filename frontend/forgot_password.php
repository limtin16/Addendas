<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
    $path.="../";
}
$path.="backend/config.php";
require_once $path;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">
    <style>
body {
    display: block !important;
}

.simple-page {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
</style>
</head>
<body>
<div class="simple-page">
    <div class="simple-box">
        <!-- 🔙 botón regresar -->
        <a href="<?= BASE_URL ?>/frontend/login.php" class="back-link">
            ← Volver al login
        </a>
        <h2>🔐 Recuperar contraseña</h2>
        <p class="description">
            Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
        </p>
        <form action="<?= BASE_URL ?>/backend/public/send_reset_link.php" method="POST">
            <input 
                type="email" 
                name="email" 
                placeholder="Tu correo electrónico" 
                required
            >
            <button type="submit" class="btn blue full">
                Enviar enlace
            </button>
        </form>
    </div>
</div>
</body>
</html>