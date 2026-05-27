<?php
$path = "";
$depth = substr_count(__DIR__, DIRECTORY_SEPARATOR) - substr_count(__DIR__, DIRECTORY_SEPARATOR) + substr_count(substr(__DIR__, strpos(__DIR__, 'addendas')), DIRECTORY_SEPARATOR);
for ($i = 0; $i < $depth; $i++) {
    $path .= "../";
}
$path .= "backend/config.php";
require_once $path;

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token inválido");
}
?>

<form action="<?= BASE_URL ?>/backend/public/update_password.php" method="POST">

    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <input type="password" name="password" placeholder="Nueva contraseña" required>

    <button type="submit">Actualizar contraseña</button>

</form>