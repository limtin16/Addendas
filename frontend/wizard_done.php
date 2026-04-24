<?php
$templateId = $_GET['template_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Addenda creada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 40px;
        }
        .card {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
        }
        a {
            display: block;
            margin-top: 15px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>✅ Addenda creada correctamente</h2>

    <p>Template ID:</p>
    <code><?= htmlspecialchars($templateId) ?></code>

    <a href="/addendas/frontend/wizard_step1.php">
        ➕ Crear nueva addenda
    </a>

   <a href="/addendas/backend/public/preview_addenda_xml.php?template_id=<?= urlencode($templateId) ?>" target="_blank">
    👁️ Ver XML de la addenda
	</a>
</div>

</body>
</html>
