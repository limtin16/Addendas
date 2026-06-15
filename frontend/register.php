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

?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro - Addendas</title>

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
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #1e7e34;
        }

        .links {
            text-align: center;
            margin-top: 15px;
        }

        .links a {
            text-decoration: none;
            color: #007bff;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .alert {
            background: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="card">

    <?php if (isset($_GET['error'])): ?>
        <div class="alert">
            <?php
            if ($_GET['error'] == 'duplicate') echo "⚠️ Este correo ya está registrado";
            elseif ($_GET['error'] == 'missing') echo "⚠️ Completa todos los campos";
            else echo "❌ Ocurrió un error";
            ?>
        </div>
    <?php endif; ?>

    <h2>Crear cuenta</h2>

    <form method="POST" action="<?= BASE_URL ?>/backend/public/register.php">
        <input type="email" name="email" placeholder="Correo" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Registrarse</button>
    </form>

    <div class="links">
        <a href="<?= BASE_URL ?>/frontend/login.php">Volver a login</a>
    </div>

</div>
<?php if (isset($_GET['error'])): ?>
<script>
setTimeout(() => {
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        alertBox.style.opacity = '0';
        alertBox.style.transition = '0.5s';
    }
}, 2500);

setTimeout(() => {
    window.location.href = "<?= BASE_URL ?>/frontend/register.php";
}, 3000);
</script>
<?php endif; ?>
</body>
</html>