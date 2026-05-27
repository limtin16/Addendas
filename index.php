<?php
require_once dirname(__DIR__) . '/addendas/backend/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AddendaFácil</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com">

<style>

body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: #f9fafb;
    color: #1f2937;
}

/* ✅ NAVBAR */
.nav {
    display: flex;
    justify-content: space-between;
    padding: 20px 40px;
    background: #fff;
    border-bottom: 1px solid #eee;
}

.nav h1 {
    font-size: 20px;
}

.nav a {
    text-decoration: none;
    color: #2563eb;
    font-weight: 600;
}

/* ✅ HERO */
.hero {
    text-align: center;
    padding: 80px 20px;
    background: #fff;
}

.hero h1 {
    font-size: 42px;
    margin-bottom: 10px;
}

.hero p {
    font-size: 18px;
    color: #555;
    max-width: 600px;
    margin: auto;
}

.btn {
    margin-top: 30px;
    display: inline-block;
    background: #2563eb;
    color: white;
    padding: 14px 26px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

/* ✅ SECTIONS */
.section {
    padding: 60px 20px;
    text-align: center;
}

.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.card {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.card h3 {
    margin-bottom: 10px;
}

/* ✅ STEPS */
.steps {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 40px;
}

.step {
    max-width: 250px;
}

/* ✅ PRICING */
.pricing {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.plan {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    width: 200px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.plan h2 {
    color: #2563eb;
}

/* ✅ CTA */
.cta {
    background: #2563eb;
    color: white;
    padding: 60px 20px;
    text-align: center;
}

.cta button {
    background: white;
    color: #2563eb;
    border: none;
    padding: 14px 26px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

/* ✅ FOOTER */
.footer {
    padding: 20px;
    text-align: center;
    font-size: 14px;
    color: #888;
}

</style>
</head>

<body>

<!-- ✅ NAV -->
<div class="nav">
    <h1>AddendaFácil</h1>
    <a href="<?= BASE_URL ?>/frontend/login.php">Iniciar sesión</a>
</div>

<!-- ✅ HERO -->
<div class="hero">
    <h1>Genera addendas sin complicaciones</h1>

    <p>
        Puedes generar tu primera addenda en segundos,
        sin registrarte. Guarda y administra tus addendas creando tu cuenta.
    </p>

    <a href="<?= BASE_URL ?>/frontend/select_mode.php" class="btn">
        Generar addenda ahora
    </a>

    <div style="margin-top:15px;">
        o
        <a href="<?= BASE_URL ?>/frontend/register.php" style="color:#2563eb; font-weight:600;">
            crea tu cuenta gratis
        </a>
    </div>
</div>

<!-- ✅ FEATURES -->
<div class="section">
    <h2>¿Por qué usar AddendaFácil?</h2>

    <div class="features">
        <div class="card">
            <h3>⚡ Rápido</h3>
            <p>Genera addendas en segundos.</p>
        </div>

        <div class="card">
            <h3>🧾 Compatible</h3>
            <p>Compatible con CFDI 4.0.</p>
        </div>

        <div class="card">
            <h3>🔁 Reutilizable</h3>
            <p>Guarda templates y reutiliza.</p>
        </div>

        <div class="card">
            <h3>✅ Sin errores</h3>
            <p>Validación automática incluida.</p>
        </div>
    </div>
</div>

<!-- ✅ HOW IT WORKS -->
<div class="section" style="background:#fff;">
    <h2>¿Cómo funciona?</h2>

    <div class="steps">
        <div class="step">
            <h3>1️⃣ Captura</h3>
            <p>Llena los datos fácilmente.</p>
        </div>

        <div class="step">
            <h3>2️⃣ Genera</h3>
            <p>El sistema crea tu addenda.</p>
        </div>

        <div class="step">
            <h3>3️⃣ Descarga</h3>
            <p>Úsala en segundos.</p>
        </div>
    </div>
</div>

<!-- ✅ PRICING -->
<div class="section">
    <h2>Compra créditos</h2>

    <div class="pricing">
        <div class="plan">
            <h2>$100</h2>
            <p>100 créditos</p>
        </div>

        <div class="plan">
            <h2>$250</h2>
            <p>260 créditos</p>
        </div>

        <div class="plan">
            <h2>$500</h2>
            <p>550 créditos</p>
        </div>
    </div>
</div>

<!-- ✅ CTA -->
<div class="cta">
    <h2>Empieza hoy mismo</h2>
    <p>Genera tus addendas en minutos</p>

    <a href="<?= BASE_URL ?>/frontend/register.php" class="btn">
        Crear cuenta gratis
    </a>
</div>

<!-- ✅ FOOTER -->
<div class="footer">
    © <?= date('Y') ?> AddendaFácil — Todos los derechos reservados
</div>

</body>
</html>