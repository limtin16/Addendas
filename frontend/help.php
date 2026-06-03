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
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Centro de Ayuda</title>

<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">

<style>

.tab-nav {
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.tab-btn {
    padding:10px 15px;
    border-radius:6px;
    border:none;
    cursor:pointer;
    background:#e5e7eb;
}

.tab-btn.active {
    background:#2563eb;
    color:white;
}

.tab {
    display:none;
}

.tab.active {
    display:block;
}

.help-img {
    width:100%;
    border-radius:10px;
    margin:15px 0;
    border:1px solid #e5e7eb;
}

.help-box {
    background:#f9fafb;
    padding:15px;
    border-radius:8px;
    margin:10px 0;
}

.help-section {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
}

.accordion {
    background: #fff;
    border-radius: 10px;
    margin-bottom: 10px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.accordion-header {
    padding: 15px;
    cursor: pointer;
    font-weight: bold;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.accordion-header:hover {
    background: #f3f4f6;
}

.accordion-body {
    display: none;
    padding: 15px;
    border-top: 1px solid #e5e7eb;
}

.accordion.active .accordion-body {
    display: block;
}

</style>

</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
<div class="container">

<h2>📘 Centro de Ayuda</h2>

<!-- ✅ TABS -->
<div class="tab-nav">
    <button class="tab-btn active" onclick="showTab('guide')">📘 Guía</button>
    <button class="tab-btn" onclick="showTab('support')">❓ Soporte</button>
</div>


<!-- ===================== -->
<!-- ✅ GUÍA -->
<!-- ===================== -->

<div id="guide" class="tab active">

<div class="accordion">

    <div class="accordion-header">
        🚀 Dashboard
        <span>+</span>
    </div>

    <div class="accordion-body">

        <a href="<?= BASE_URL ?>/frontend/help/images/dashboard.png" target="_blank" class="help-img">
            <img src="<?= BASE_URL ?>/frontend/help/images/dashboard.png" alt="Dashboard" class="help-img">
        </a>

        <div class="help-box">
            Aquí puedes ver tus créditos y acceder a las funciones principales.
        </div>

    </div>

</div>


<div class="accordion">

<div class="accordion-header">
    ✍️ Crear Addenda
    <span>+</span>
</div>

<div class="accordion-body">

<a href="<?= BASE_URL ?>/frontend/help/images/select_mode.png" target="_blank" class="help-img">
    <img src="<?= BASE_URL ?>/frontend/help/images/select_mode.png" alt="Seleccionar modo" class="help-img">
</a>

<ul>
    <li><b>Manual:</b> paso a paso</li>
    <li><b>Subir CFDI:</b> automático</li>
    <li><b>XSD:</b> avanzado</li>
</ul>

</div>
</div>


<div class="accordion">

<div class="accordion-header">
    📁 Templates
    <span>+</span>
</div>

<div class="accordion-body">

<a href="<?= BASE_URL ?>/frontend/help/images/templates.png" target="_blank" class="help-img">
    <img src="<?= BASE_URL ?>/frontend/help/images/templates.png" alt="Templates" class="help-img">
</a>

<div class="help-box">
    Usa templates para ahorrar tiempo. Requiere créditos.
</div>

</div>
</div>


<div class="accordion">

<div class="accordion-header">
    💳 Comprar créditos
    <span>+</span>
</div>

<div class="accordion-body">

<a href="<?= BASE_URL ?>/frontend/help/images/buy_credits.png" target="_blank" class="help-img">
    <img src="<?= BASE_URL ?>/frontend/help/images/buy_credits.png" alt="Comprar créditos" class="help-img">
</a>

<ol>
    <li>Selecciona paquete</li>
    <li>Paga con PayPal</li>
    <li>Espera confirmación</li>
</ol>

</div>
</div>


<div class="accordion">

<div class="accordion-header">
    ✅ Pago exitoso
    <span>+</span>
</div>

<div class="accordion-body">

<a href="<?= BASE_URL ?>/frontend/help/images/payment_success.png" target="_blank" class="help-img">
    <img src="<?= BASE_URL ?>/frontend/help/images/payment_success.png" alt="Pago exitoso" class="help-img">
</a>

<p style="color:#6b7280;">
Los créditos pueden tardar unos segundos en reflejarse.
</p>

</div>
</div>

<div class="accordion">

<div class="accordion-header">
    ⚠️ Problemas comunes
    <span>+</span>
</div>

<div class="accordion-body">

<div class="help-box">
❌ No puedes crear → No tienes créditos  
</div>

<div class="help-box">
❌ No aparecen créditos → Espera o recarga  
</div>

</div>
</div>


<!-- ===================== -->
<!-- ✅ SOPORTE -->
<!-- ===================== -->

<div id="support" class="tab">

<div class="card">

<h3>❓ Soporte</h3>

<p class="note">
¿Tienes algún problema? Escríbenos y te ayudamos lo antes posible.
</p>

<hr>

<form action="<?= BASE_URL ?>/backend/public/send_support.php" method="POST">

    <label><b>Tu correo</b></label><br>
    <input 
        type="email" 
        name="email" 
        required 
        value="<?= $_SESSION['user_email'] ?? '' ?>"
        style="width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px;"
    >

    <label><b>Describe tu problema</b></label><br>
    <textarea 
        name="message" 
        required 
        rows="6"
        style="width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px;"
    ></textarea>

    <br>

    <button type="submit" class="btn blue">
        📩 Enviar solicitud
    </button>

</form>

<br>

<?php if (!empty($_SESSION['user_id'])): ?>

<a href="<?= BASE_URL ?>/frontend/dashboard.php" class="btn green">
    ➡ Volver al dashboard
</a>

<?php else: ?>

<a href="<?= BASE_URL ?>/frontend/select_mode.php" class="btn green">
    🏠 Volver al inicio
</a>

<?php endif; ?>

</div>

</div>

</div>
</div>


<!-- ✅ SCRIPT TABS -->
<script>

function showTab(tabId) {

    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

    document.getElementById(tabId).classList.add('active');

    event.target.classList.add('active');
}

document.querySelectorAll('.accordion-header').forEach(header => {

    header.addEventListener('click', () => {

        document.querySelectorAll('.accordion').forEach(acc => {
            acc.classList.remove('active');
        });

        header.parentElement.classList.add('active');

    });

});

</script>

</body>
</html>