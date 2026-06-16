<?php
require_once __DIR__ . '/backend/config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Genera addendas CFDI 4.0 en minutos. Sube XML, XSD o crea addendas desde cero de forma automática con AddendaFácil.">
<meta name="keywords" content="addenda CFDI, generar addenda, CFDI XML addenda, cómo hacer addenda, herramienta addenda">
<meta property="og:title" content="AddendaFácil - Genera addendas CFDI fácilmente">
<meta property="og:description" content="Crea addendas CFDI 4.0 automáticamente desde XML o XSD">
<meta property="og:type" content="website">
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/frontend/assets/logo_addendafacil.png">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "AddendaFácil",
  "applicationCategory": "BusinessApplication",
  "description": "Plataforma para generar addendas CFDI desde XML, XSD o plantillas predefinidas",
  "operatingSystem": "Web",
  "url": "https://www.addendafacil.com"
}
</script>
<title>AddendaFácil</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

<style>

/* ✅ RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;

    background:
        radial-gradient(circle at 20% 30%, #dbeafe 0%, transparent 40%),
        radial-gradient(circle at 80% 70%, #e9d5ff 0%, transparent 40%),
        linear-gradient(180deg, #f8fafc, #ffffff);

    height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ✅ NAVBAR */
.nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 60px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.nav h1 {
    font-size: 20px;
    font-weight: 600;
}

.nav a {
    text-decoration: none;
    color: #2563eb;
    font-weight: 600;
}

/* ✅ HERO FULLSCREEN */
.hero {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* ✅ CARD CENTRAL */
.card {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(10px);
    padding: 60px 50px;
    border-radius: 18px;
    max-width: 650px;
    width: 100%;
    box-shadow: 0 30px 60px rgba(0,0,0,0.12);
    text-align: center;
}

/* ✅ TEXTOS */
.card h1 {
    font-size: 42px;
    font-weight: 600;
    margin-bottom: 15px;
}

.card p {
    font-size: 18px;
    color: #6b7280;
    margin-bottom: 25px;
}

/* ✅ BOTÓN */
.btn-main {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: white;
    padding: 16px 32px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    transition: 0.25s;
}

.btn-main:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

/* ✅ LINKS */
.secondary {
    margin-top: 15px;
    font-size: 14px;
}

.secondary a {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
}

/* ✅ FEATURES INLINE */
.features {
    display: flex;
    justify-content: center;
    margin-top: 40px;
    gap: 15px;
    text-align: center;
}

.features div {
    background: #eff6ff;
    padding: 12px 15px;
    border-radius: 10px;
    font-size: 13px;
}

/* ✅ FOOTER */
.footer {
    text-align: center;
    padding: 15px;
    font-size: 12px;
    color: #9ca3af;
}

.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
}

.seo-section {
    max-width: 800px;
    margin: 60px auto;
    background: rgba(255,255,255,0.8);
    padding: 30px;
    border-radius: 12px;
    font-size: 15px;
    line-height: 1.6;
}

.seo-section h2 {
    margin-bottom: 15px;
}

.seo-section h3 {
    margin-top: 20px;
}

.seo-wrapper {
    max-width: 800px;
    margin: 40px auto;
    text-align: center;
}

.seo-toggle {
    background: #f1f5f9;
    border: 1px solid #e5e7eb;
    padding: 10px 15px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
}

.seo-toggle:hover {
    background: #e2e8f0;
}

.seo-content {
    max-height: 0;
    overflow: hidden;
    transition: all 0.4s ease;
}

.seo-content.open {
    max-height: 2000px;
    margin-top: 20px;
}
</style>
</head>

<body>

<!-- ✅ NAV -->
<div class="nav">
    <div style="display:flex; align-items:center; gap:10px;">
        <img src="<?= BASE_URL ?>/frontend/assets/logo_addendafacil.png" alt="AddendaFácil logo" height="35">
        <span style="font-weight:600;">AddendaFácil</span>
    </div>
    <a href="<?= BASE_URL ?>/frontend/login.php">Iniciar sesión</a>
</div>

<!-- ✅ HERO FULL -->
<div class="hero">

    <div class="card">

        <h1>Genera addendas en minutos</h1>

        <p>
            Olvídate del proceso complicado.  
            Crea, valida y descarga addendas CFDI 4.0 de forma rápida y segura.
        </p>

        <a href="<?= BASE_URL ?>/frontend/register.php" class="btn-main">
            Crear cuenta gratis 🚀
        </a>
        <div style="margin-top:20px; font-size:13px; color:#6b7280;">
            ✅ Sin tarjeta · ⚡ Acceso inmediato · 🧾 CFDI 4.0 compatible
        </div>

        <div class="secondary">
            <div style="margin-bottom:8px;">
                <!--
                <a href="<?= BASE_URL ?>/frontend/select_mode.php">
                    Probar sin cuenta →
                </a>
                -->
            </div>

            <div style="font-size:13px; color:#6b7280;">
                ¿Ya tienes cuenta? 
                <a href="<?= BASE_URL ?>/frontend/login.php" style="font-weight:600;">
                    Iniciar sesión
                </a>
            </div>
        </div>

        <!-- ✅ FEATURES compactas -->
        <div class="features">
            <div>⚡ Rápido</div>
            <div>✅ Validado</div>
            <div>🔁 Reutilizable</div>
        </div>
        <div>
            <p><br>
                AddendaFácil es una herramienta para generar addendas CFDI automáticamente sin necesidad de editar XML manualmente.
            </p>
        </div>
    </div>

</div>
<div class="seo-wrapper">
    <button class="seo-toggle" onclick="toggleSEO()">
        📘 ¿Qué es una addenda y cómo funciona?
    </button>
    <div id="seo-content" class="seo-content">
    <h2>¿Cómo generar una addenda en CFDI?</h2>
    <p>
        Una addenda es información adicional que algunas empresas requieren en una factura electrónica (CFDI).
        Generalmente se agrega dentro del XML y puede incluir datos como órdenes de compra, códigos internos o condiciones comerciales.
    </p>
    <p>
        Tradicionalmente, crear una addenda requiere editar manualmente el XML o conocer estructuras técnicas.
        Sin embargo, herramientas como <strong>AddendaFácil</strong> permiten generarlas automáticamente sin conocimientos técnicos.
    </p>
    <h3>Opciones para crear una addenda</h3>
    <ul>
        <li>Editar manualmente el XML del CFDI</li>
        <li>Usar un archivo XSD proporcionado por el cliente</li>
        <li>Subir un XML con addenda existente</li>
        <li>Utilizar una plataforma automática como AddendaFácil</li>
    </ul>
    </div>
</div>
<div class="seo-wrapper">
    <button class="seo-toggle" onclick="toggleSEO1()">
        📘 ¿Preguntas Frecuentes sobre addendas?
    </button>
    <div id="seo-content1" class="seo-content">
    <h2>Preguntas frecuentes sobre addendas CFDI</h2>
    <h3>¿Cómo agregar una addenda a una factura?</h3>
    <p>
        Puedes hacerlo editando el XML manualmente o usando herramientas como AddendaFácil que automatizan el proceso.
    </p>
    <h3>¿Qué es una addenda en CFDI?</h3>
    <p>
        Es una sección opcional dentro del XML que contiene información adicional requerida por el receptor.
    </p>
    <h3>¿Dónde puedo generar addendas fácilmente?</h3>
    <p>
        Existen plataformas como AddendaFácil que permiten generar, validar y descargar CFDIs con addenda en minutos.
    </p>
    </div>
</div>

<!-- ✅ FOOTER -->
<div class="footer">
    © <?= date('Y') ?> AddendaFácil
<footer>
<a href="<?= BASE_URL ?>/frontend/privacy.php"> Privacidad</a>
<a href="<?= BASE_URL ?>/frontend/help.php">Ayuda</a>
</footer>
</div>
<script>
function toggleSEO() {
    const content = document.getElementById('seo-content');

    content.classList.toggle('open');
}
function toggleSEO1() {
    const content = document.getElementById('seo-content1');

    content.classList.toggle('open');
}
</script>
</body>
</html>