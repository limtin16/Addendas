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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Iniciar sesión - AddendaFácil</title>
    <meta name="description" content="Accede a tu cuenta de AddendaFácil para generar addendas CFDI de forma automática.">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/frontend/assets/logo_addendafacil.png">
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
        .login-info {
            margin-bottom: 20px;
            text-align: center;
            color: #555;
        }
        .logo {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo img {
            width: 90px;
            height: auto;
        }
    </style>
</head>

<body>

<div class="card">
<?php if (isset($_GET['error'])): ?>
    <div class="login-error">
        ❌ Credenciales incorrectas
    </div>
<?php endif; ?>
    <div class="logo">
        <img src="<?= BASE_URL ?>/frontend/assets/logo_addendafacil.png" alt="AddendaFácil logo">
        <p style="text-align:center; font-size:12px; color:#6b7280;">
            Sitio oficial de AddendaFácil
        </p>
        <p style="text-align:center; font-size:11px; color:#999; margin-top:20px;">
            © <?= date('Y') ?> AddendaFácil · addendafacil.com
        </p>
    </div>
    <h2>Iniciar sesión</h2>

    <div class="login-info">
        <p>
            AddendaFácil es una plataforma para generar addendas CFDI de forma automática.
        </p>
        <p>
            Inicia sesión para continuar.
        </p>
    </div>
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
        <!--
        <a href="<?= BASE_URL ?>/frontend/select_mode.php">Entrar como visitante</a>
        -->
    </div>
    <div>
        <p style="text-align:center; font-size:13px; margin-top:15px;">
        <a href="<?= BASE_URL ?>/index.php"> Inicio</a> 
        <a href="<?= BASE_URL ?>/frontend/blog/como-crear-addenda-cfdi.php">¿Qué es una addenda?</a>
</p>
    </div>
</div>
<script>
    if (window.location.search.includes('error')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    setTimeout(() => {
        const alert = document.querySelector('.login-error');
        if (alert) {
            alert.style.transition = "opacity 0.5s";
            alert.style.opacity = "0";
        }
    }, 2500);
</script>
</body>
</html>