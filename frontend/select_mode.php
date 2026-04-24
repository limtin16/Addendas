<?php
// select_mode.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Addenda – Seleccionar modo</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    padding: 40px;
}

.container {
    max-width: 800px;
    margin: auto;
}

h1 {
    text-align: center;
}

.mode-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 40px;
}

.mode-card {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #ddd;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.mode-card:hover {
    border-color: #0078d4;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.mode-card.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.mode-icon {
    font-size: 40px;
    margin-bottom: 15px;
}

.mode-title {
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 10px;
}

.mode-desc {
    font-size: 14px;
    color: #555;
}
</style>
</head>

<body>

<div class="container">

<h1>¿Cómo deseas crear la addenda?</h1>

<div class="mode-grid">

    <!-- OPCIÓN 1: MANUAL -->
    <div class="mode-card" onclick="goManual()">
        <div class="mode-icon">🛠</div>
        <div class="mode-title">Crear manualmente</div>
        <div class="mode-desc">
            Usa el wizard paso a paso para definir la addenda desde cero.
        </div>
    </div>

    <!-- OPCIÓN 2: SUBIR XML -->
    <div class="mode-card" onclick="goUpload()">
        <div class="mode-icon">📄</div>
        <div class="mode-title">Subir addenda o CFDI</div>
        <div class="mode-desc">
            Analiza una addenda existente y recrea la plantilla automáticamente.
        </div>
    </div>

    <!-- OPCIÓN 3: XSD (DESHABILITADA) -->
    <div class="mode-card disabled">
        <div class="mode-icon">📐</div>
        <div class="mode-title">Subir XSD</div>
        <div class="mode-desc">
            Genera la addenda a partir de un esquema XSD. (Próximamente)
        </div>
    </div>

</div>

</div>

<script>
function goManual() {
    // Flujo actual existente
    window.location.href = '/addendas/frontend/wizard_step1.html';
}

function goUpload() {
    // Nuevo flujo que implementaremos ahora
    window.location.href = '/addendas/frontend/upload_addenda.php';
}
</script>

</body>
</html>