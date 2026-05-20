<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Addendas</title>
</head>
<body>

<h2>Iniciar sesión</h2>

<form method="POST" action="/addendas/backend/public/login.php">
    <input type="email" name="email" placeholder="Correo" required>
    <br><br>
    <input type="password" name="password" placeholder="Contraseña" required>
    <br><br>
    <button type="submit">Login</button>
</form>

<br>

<h3>¿No tienes cuenta?</h3>
<a href="/addendas/frontend/register.php">Registrarse</a>

<br><br>

<h3>O continuar como visitante</h3>
<a href="/addendas/frontend/select_mode.php">Entrar como visitante</a>

</body>
</html>