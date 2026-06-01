<?php
require_once __DIR__ . '/backend/config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

</style>
</head>

<body>

<!-- ✅ NAV -->
<div class="nav">
    <h1>🚀 AddendaFácil</h1>
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
<!--
        <div class="secondary">
            <div style="margin-bottom:8px;">
                <a href="<?= BASE_URL ?>/frontend/select_mode.php">
                    Probar sin cuenta →
                </a>
            </div>
-->
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

    </div>

</div>

<!-- ✅ FOOTER -->
<div class="footer">
    © <?= date('Y') ?> AddendaFácil
</div>

</body>
</html>