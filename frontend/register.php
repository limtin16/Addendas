<!DOCTYPE html>
<html>
<head>
    <title>Registro</title>
</head>
<body>

<h2>Crear cuenta</h2>

<form method="POST" action="/addendas/backend/public/register.php">
    <input type="email" name="email" placeholder="Correo" required>
    <br><br>
    <input type="password" name="password" placeholder="Contraseña" required>
    <br><br>
    <button type="submit">Registrarse</button>
</form>

<br>
<a href="/addendas/frontend/login.php">Volver a login</a>

</body>
</html>