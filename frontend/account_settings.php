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

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Configuración de cuenta</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">

    <div class="container">

        <h2>⚙️ Configuración de cuenta</h2>

        <!-- CAMBIAR CORREO -->
        <div class="card">
    <h3>📧 Cambiar correo</h3>

    <form action="<?= BASE_URL ?>/backend/public/update_email.php" method="POST">

        <input type="email" name="email" placeholder="Nuevo correo" required>

        <input type="email" name="email_confirm" placeholder="Confirmar correo" required>

        <button class="btn blue">Actualizar correo</button>
    </form>
</div>

        <!-- CAMBIAR PASSWORD -->
        <div class="card">
    <h3>🔒 Cambiar contraseña</h3>

    <form action="<?= BASE_URL ?>/backend/public/update_password_user.php" method="POST">

            <input type="password" name="current_password" placeholder="Contraseña actual" required>

            <input type="password" name="new_password" placeholder="Nueva contraseña" required>

            <input type="password" name="new_password_confirm" placeholder="Confirmar nueva contraseña" required>

            <button class="btn green">Cambiar contraseña</button>
        </form>
    </div>

    </div>

</div>

</body>
</html>