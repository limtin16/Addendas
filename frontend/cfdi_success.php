<?php
session_start();

if (!isset($_SESSION['generated_cfdi_xml'])) {
    die("No hay CFDI generado");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>CFDI Generado</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .btn {
            display: block;
            margin: 10px 0;
            padding: 12px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
        }

        .download {
            background: #007bff;
        }

        .save {
            background: #28a745;
        }
    </style>
</head>

<body>

<div class="box">

    <h2>✅ CFDI generado con éxito</h2>

    <p>
    🆔 ID de recuperación:<br>
    <strong><?= $_SESSION['generated_cfdi_token'] ?></strong>
    </p>

    <p style="font-size:13px; color:#666;">
    Guarda este ID. Podrás recuperar tu CFDI más adelante.
    </p>

    <a href="/addendas/backend/public/download_cfdi.php" class="btn download">
        Descargar CFDI
    </a>

    <?php if (empty($_SESSION['using_template'])): ?>

    <h3>¿Guardar como template?</h3>

    <form action="/addendas/backend/public/save_template_db.php" method="POST">
        <input type="text" name="name" placeholder="Nombre del template" required>
        <button class="btn save">Guardar template</button>
    </form>

    <?php endif; ?>

</div>
 <?php unset($_SESSION['using_template']); ?>

</body>
<script>
function confirmGuardarTemplate() {

    return confirm(
        "¿Estás seguro de guardar este template?\n\n" +
        "Si no has descargado el CFDI, podrías perderlo."
    );
}
</script>
</html>