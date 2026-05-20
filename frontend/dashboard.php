<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /addendas/frontend/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="/addendas/frontend/assets/styles.css">
</head>

<body>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">

    <div class="container">

        <div class="welcome">
            <h2>Bienvenido 👋</h2>
            <p>Selecciona una acción para comenzar</p>
        </div>

        <div class="cards">

            <div class="card">
                <h3>🆕 Crear Addenda</h3>
                <p>Genera una nueva addenda desde cero</p>
                <a href="/addendas/frontend/select_mode.php" class="btn blue">
                    Crear
                </a>
            </div>

            <div class="card">
                <h3>📁 Mis Templates</h3>
                <p>Reutiliza templates guardados</p>
                <a href="/addendas/frontend/templates_list.php" class="btn gray">
                    Ver templates
                </a>
            </div>

            <div class="card">
                <h3>📑 CFDIs generados</h3>
                <p>Consulta y descarga CFDIs generados</p>
                <a href="/addendas/frontend/cfdi_list.php" class="btn gray">
                    Ver historial
                </a>
            </div>

        </div>

    </div>

</div>

</body>
</html>