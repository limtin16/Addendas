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

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token inválido");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-18362690034"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-18362690034');
</script>
    <meta charset="UTF-8">
    <title>Actualizar contraseña</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles.css">
</head>

<body>

<div class="simple-page">

    <div class="simple-box">

        <!-- 🔙 volver -->
        <a href="<?= BASE_URL ?>/frontend/login.php" class="back-link">
            ← Volver al login
        </a>

        <h2>🔐 Nueva contraseña</h2>

        <p class="description">
            Ingresa tu nueva contraseña para continuar.
        </p>

         <form action="<?= BASE_URL ?>/backend/public/update_password.php" method="POST">

            <input 
                type="hidden" 
                name="token" 
                value="<?= htmlspecialchars($token) ?>"
            >

            <input 
                type="password" 
                name="password" 
                placeholder="Nueva contraseña" 
                required
            >

            <button type="submit" class="btn blue full">
                Actualizar contraseña
            </button>

        </form>

    </div>

</div>

</body>
</html> 