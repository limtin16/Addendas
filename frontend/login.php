<?php

$path="";
$root = __DIR__;
echo $root;
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),$root),100),'\\'));
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$path.="backend/config.php";
//echo $path;
exit;
include $path;

session_start(); 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Addendas</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            width: 320px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .links {
            text-align: center;
            margin-top: 15px;
        }

        .links a {
            display: block;
            margin: 5px;
            text-decoration: none;
            color: #007bff;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .guest {
            margin-top: 20px;
            text-align: center;
        }

        .guest a {
            color: #555;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Iniciar sesión</h2>

    <form method="POST" action="<?= BASE_URL ?>/backend/public/login.php">
        <input type="email" name="email" placeholder="Correo" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Login</button>
    </form>

    <div class="links">
        <a href="<?= BASE_URL ?>/frontend/register.php">Crear cuenta</a>
    </div>

    <div class="links">
        <a href="<?= BASE_URL ?>/frontend/forgot_password.php">
        ¿Olvidaste tu contraseña?
        </a>
    </div>

    <div class="guest">
        <a href="<?= BASE_URL ?>/frontend/select_mode.php">Entrar como visitante</a>
    </div>

</div>

</body>
</html>