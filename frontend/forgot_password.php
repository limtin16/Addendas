<?php
$path = "";
$depth = substr_count(__DIR__, DIRECTORY_SEPARATOR) - substr_count(__DIR__, DIRECTORY_SEPARATOR) + substr_count(substr(__DIR__, strpos(__DIR__, 'addendas')), DIRECTORY_SEPARATOR);
for ($i = 0; $i < $depth; $i++) {
    $path .= "../";
}
$path .= "backend/config.php";
require_once $path;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Recuperar contraseña</title>
</head>
<body>

<h2>Recuperar contraseña</h2>

<form action="<?= BASE_URL ?>/backend/public/send_reset_link.php" method="POST">
    <input type="email" name="email" placeholder="Tu correo" required>
    <button type="submit">Enviar enlace</button>
</form>

</body>
</html>