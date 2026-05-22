<?php
session_start();
unset($_SESSION['using_template']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Addenda – Seleccionar modo</title>

<link rel="stylesheet" href="/addendas/frontend/assets/styles.css">

</head>

<body>

<!-- ✅ SIDEBAR -->
<?php include __DIR__ . '/partials/sidebar.php'; ?>

<!-- ✅ CONTENIDO -->
<div class="main">

    <div class="container">

        <h2>¿Cómo deseas crear la addenda?</h2>

        <div class="mode-grid">

            <!-- MANUAL -->
            <div class="mode-card" onclick="goManual()">
                <div class="mode-icon">🛠</div>
                <div class="mode-title">Crear manualmente</div>
                <div class="mode-desc">
                    Usa el wizard paso a paso para definir la addenda desde cero.
                </div>
            </div>

            <!-- SUBIR XML -->
            <div class="mode-card" onclick="goUpload()">
                <div class="mode-icon">📄</div>
                <div class="mode-title">Subir addenda o CFDI</div>
                <div class="mode-desc">
                    Analiza una addenda existente y recrea la plantilla automáticamente.
                </div>
            </div>

            <!-- XSD -->
            <div class="mode-card" onclick="goXsd()">
                <div class="mode-icon">📐</div>
                <div class="mode-title">Subir XSD</div>
                <div class="mode-desc">
                    Genera la addenda automáticamente a partir de un archivo XSD.
                </div>
            </div>

        </div>

    </div>

</div>

<script>
function goManual() {
    window.location.href = '/addendas/frontend/wizard_step1.php';
}

function goUpload() {
    window.location.href = '/addendas/frontend/upload_addenda.php';
}

function goXsd() {
    window.location.href = '/addendas/frontend/upload_xsd.php';
}
</script>

</body>
</html>