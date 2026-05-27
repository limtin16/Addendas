<!DOCTYPE html>
<html>
<head>
    <title>Recuperar contraseña</title>
</head>
<body>

<h2>Recuperar contraseña</h2>

<form action="/backend/public/send_reset_link.php" method="POST">
    <input type="email" name="email" placeholder="Tu correo" required>
    <button type="submit">Enviar enlace</button>
</form>

</body>
</html>