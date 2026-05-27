<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token inválido");
}
?>

<form action="<?= $base ?>/backend/public/update_password.php" method="POST">

    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <input type="password" name="password" placeholder="Nueva contraseña" required>

    <button type="submit">Actualizar contraseña</button>

</form>