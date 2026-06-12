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

code {
    background: #111;
    color: #00ffcc;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
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
            📖 ¿Qué es AddendaFacil?
            <span>+</span>
        </div>
        <div class="accordion-body">
        <div class="help-box">
        AddendaFacil es una plataforma que te permite generar addendas de CFDI de forma rápida, estructurada y sin errores técnicos.
        </div>
        <p>
        Una <b>addenda</b> es información adicional que algunas empresas requieren dentro del CFDI (por ejemplo: órdenes de compra, remisiones, condiciones comerciales).
        </p>
        <p>
        Este sistema elimina la necesidad de editar XML manualmente y automatiza la generación con base en plantillas reutilizables.
        </p>
    </div>
</div>

<div class="accordion">
    <div class="accordion-header">
        🧭 Flujo completo paso a paso
        <span>+</span>
    </div>
    <div class="accordion-body">
        <ol>
        <li>Elegir modo</li>
        <li>Subir archivo requerido o en modo manual Definir estructura campos y grupos</li>
        <li>Subir CFDI</li>
        <li>Llenar datos o aplicar autofill automático</li>
        <li>Insertar addenda</li>
        <li>Descargar CFDI final</li>
        </ol>
            <div class="help-box">
            💡 Consejo: Una vez creado un template, puedes reutilizarlo para generar múltiples addendas sin volver a configurarlo.
            </div>
    </div>
</div>

<div class="accordion">
    <div class="accordion-header">
        🧾 Crear factura y agregar Addenda (flujo completo)
        <span>+</span>
    </div>
    <div class="accordion-body">
        <p>
        Para generar una addenda primero necesitas una factura electrónica (CFDI). Aquí te explicamos todo el proceso completo paso a paso.
        </p>
        <hr>
        <h4>✅ 1. Generar tu factura (CFDI)</h4>
        <p>Puedes generarla de dos formas:</p>
        <h5>🔹 Opción A: Usar tu sistema de facturación (PAC)</h5>
        <ul>
        <li>Genera tu factura normalmente en tu sistema</li>
        <li>Descarga el archivo <b>XML</b></li>
        </ul>
        <div class="help-box">
            ✅ Recomendado si ya tienes un sistema de facturación
        </div>
        ---
        <h5>🔹 Opción B: Usar el portal gratuito del SAT</h5>
        <p>
        Puedes generar una factura directamente en el SAT usando su herramienta oficial:
        </p>
        <a href="https://www.google.com/goto?url=CAESmgEB7keqTWvfdzLTthkGMnHnrr0OAKG4izdr26_RwHtDDUqw9wXbwCZiGpPby9myRF58iS1GEVWJGmz46rvyAahL_8NKm8EoKEJA030YnZg4gmF-xxY7jdVdWtO_V133NNoCILJrmHV5lYJsTtj0iS4IcscyAJl7d-gSFm3upajUoYYZMStdbuXNUHuyIInAjfM0wAIos3pr_eko" target="_blank" class="btn blue small">
            📘 Ver manual oficial del SAT
        </a>
        <div class="help-box">
            💡 Solo necesitas generar una factura simple con al menos un concepto.
        </div>
        ---
        <h4>📥 2. Descargar el archivo XML</h4>
        <p>
        Una vez generada la factura:
        </p>
        <ul>
        <li>Descarga el archivo <b>XML</b> (es el más importante)</li>
        <li>Guárdalo en tu equipo</li>
        </ul>
        <div class="help-box">
            ⚠️ Este archivo es el que usarás en AddendaFacil
        </div>
        ---
        <h4>⚙️ 3. Ir a AddendaFacil</h4>
        <p>
        Regresa a la plataforma y selecciona:
        </p>
        <ul>
        <li>Crear Addenda</li>
        <li>Elige el modo que necesitas (Manual, XML o XSD)</li>
        </ul>
        ---
        <h4>📤 4. Subir tu CFDI</h4>
        <p>
        Sube el archivo XML que descargaste del SAT o tu sistema de facturación.
        </p>
        <div class="help-box">
            💡 El sistema puede leer automáticamente datos del CFDI para llenar campos.
        </div>
        ---
        <h4>🧩 5. Generar la Addenda</h4>
        <p>
        Dependiendo del modo seleccionado:
        </p>
        <ul>
        <li>Modo manual: defines la estructura paso a paso</li>
        <li>Modo XML: replicas una addenda existente</li>
        <li>Modo XSD: generas estructura desde archivo técnico</li>
        </ul>
        <p>
        Después, el sistema insertará la addenda dentro del CFDI.
        </p>
        ---
        <h4>📥 6. Descargar el CFDI final</h4>
        <p>
        Obtendrás un archivo XML con la addenda incluida.
        </p>
        <div class="help-box">
            ✅ Este es el archivo final que debes enviar a tu cliente <br>
            ✅ Generalmente los sistemas de recepcion de facturas (EJ. COFIDI, Portal Pegaso etc) tienen un checkbox para agregar addenda, en este caso no debes marcarlo ya que tu factura ya la contiene
        </div>
        ---
        <h4>⚠️ Consideraciones importantes</h4>
        <div class="help-box">
            ❗ El SAT no agrega addendas automáticamente
        </div>
        <div class="help-box">
            ❗ Las addendas son requeridas por clientes específicos
        </div>
        <div class="help-box">
            ❗ El CFDI debe estar previamente generado antes de usar esta plataforma
        </div>
        ---
        <h4>✅ Resumen rápido</h4>
        <div class="help-box">
            1. Genera tu factura (SAT o PAC)  
            <br>2. Descarga el XML  
            <br>3. Súbelo en AddendaFacil  
            <br>4. Genera la addenda  
            <br>5. Descarga el CFDI final listo
        </div>
    </div>
</div>

<div class="accordion">
    <div class="accordion-header">
        ✍️ Crear Addenda
        <span>+</span>
    </div>
    <div class="accordion-body">
        <p>
        Existen tres formas de generar una addenda en el sistema:
        </p>
        <ul>
        <li><b>Modo Manual:</b> crear desde cero paso a paso</li>
        <li><b>Modo XML:</b> analizar una addenda existente</li>
        <li><b>Modo XSD:</b> generar desde esquema estructural</li>
        </ul>
        <div class="help-box">
            💡 Cada modo está pensado para diferentes niveles de experiencia.
        </div>
    </div>
</div>

<div class="accordion">
    <div class="accordion-header">
        🧱 Modo Manual (Wizard paso a paso)
        <span>+</span>
    </div>
    <div class="accordion-body">
        <p>
        Este modo te permite construir una addenda desde cero usando el asistente del sistema.
        </p>
        <ol>
        <li>Crear template</li>
        <li>Definir estructura (root, prefix, namespace)</li>
        <li>Agregar campos simples</li>
        <li>Configurar grupos (ej: Conceptos / part)</li>
        </ol>
        <div class="help-box">
        ✅ Ideal para usuarios que no tienen una addenda previa.
        </div>
        <div class="help-box">
            ⚠️ Requiere conocimiento básico de la estructura deseada.
        </div>
    </div>
</div>

<div class="accordion">
    <div class="accordion-header">
        📄 Modo XML (análisis automático)
        <span>+</span>
    </div>
    <div class="accordion-body">
        <p>
        Este modo permite subir un XML que ya contiene una addenda para que el sistema la analice automáticamente.
        </p>
        ---
        <h4>🔍 ¿Qué hace este modo?</h4>
        <ul>
        <li>Detecta la estructura de la addenda</li>
        <li>Identifica nodos y atributos</li>
        <li>Genera automáticamente un template equivalente</li>
        </ul>
        ---
        <h4>📥 ¿Qué puedes subir?</h4>
        <ul>
        <li>Un CFDI con addenda incluida</li>
        <li>Un XML con estructura de addenda</li>
        </ul>
        ---
        <h4>📤 Resultado</h4>
        <div class="help-box">
            El sistema reproduce la estructura en un template reutilizable.
        </div>
        ---
        <h4>🎯 ¿Cuándo usarlo?</h4>
        <ul>
        <li>Cuando ya tienes una addenda válida</li>
        <li>Cuando trabajas con clientes existentes</li>
        <li>Cuando no quieres crear estructura manualmente</li>
        </ul>
    </div>
</div>

<div class="accordion">
    <div class="accordion-header">
        📐 Modo XSD (generación desde esquema)
        <span>+</span>
    </div>
    <div class="accordion-body">
        <p>
        Este modo permite generar una addenda a partir de un archivo XSD (especificación formal del cliente).
        </p>
        ---
        <h4>📥 Entrada</h4>
        <ul>
        <li>Archivo XSD proporcionado por el cliente</li>
        </ul>
        ---
        <h4>⚙️ ¿Qué hace el sistema?</h4>
        <ul>
        <li>Analiza la estructura del XSD</li>
        <li>Detecta elementos y atributos</li>
        <li>Construye automáticamente la estructura del template</li>
        </ul>
        ---
        <h4>📤 Resultado</h4>
        <div class="help-box">
            Se genera una addenda estructural lista para ser llenada o automatizada.
        </div>
        ---
        <h4>⚠️ Consideraciones</h4>
        <ul>
        <li>Puede requerir ajustes manuales</li>
        <li>Algunos XSD incluyen reglas complejas</li>
        </ul>
        ---
        <h4>🎯 ¿Cuándo usarlo?</h4>
        <ul>
        <li>Cuando el cliente proporciona un XSD oficial</li>
        <li>Cuando no tienes un ejemplo XML</li>
        <li>Cuando necesitas máxima precisión técnica</li>
        </ul>
    </div>
</div>

<div class="accordion">
    <div class="accordion-header">
        ⚙️ Autofill automático (source)
        <span>+</span>
    </div>
    <div class="accordion-body">
        <p>
        El sistema puede llenar campos automáticamente usando información del CFDI.
        </p>
        <ul>
        <li><code>@cfdi:Comprobante/Fecha</code></li>
        <li><code>@pago20:DoctoRelacionado/IdDocumento</code></li>
        </ul>
        <div class="help-box">
            💡 Puedes usar valores constantes como:
            <br>
            <code>#const:1</code>
        </div>
        <p>
        Esto permite automatizar completamente la generación de la addenda sin intervención manual.
        </p>
    </div>
</div>

<div class="accordion">
    <div class="accordion-header">
        📁 Uso de Templates
        <span>+</span>
    </div>
    <div class="accordion-body">
        <p>
        Los templates permiten reutilizar estructuras de addenda para diferentes CFDIs.
        </p>
        <ul>
        <li>Ahorro de tiempo</li>
        <li>Evita errores manuales</li>
        <li>Estandariza procesos</li>
        </ul>
        <div class="help-box">
            ✅ Puedes crear múltiples templates por cliente.
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
        ⚠️ Errores comunes y soluciones
        <span>+</span>
    </div>
<div class="accordion-body">
    <div class="help-box">
        <b>Error:</b> No se llenan campos<br>
        <b>Solución:</b> Verifica que tengan "source"
    </div>
    <div class="help-box">
        <b>Error:</b> Addenda vacía<br>
        <b>Solución:</b> Revisa estructura del template
    </div>
        <div class="help-box">
            <b>Error:</b> CFDI inválido<br>
            <b>Solución:</b> Verifica namespaces y estructura XML
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
        const accordion = header.parentElement;
        const icon = header.querySelector('span');
        const isActive = accordion.classList.contains('active');
        // Cerrar todos y resetear iconos
        document.querySelectorAll('.accordion').forEach(acc => {
            acc.classList.remove('active');
            acc.querySelector('.accordion-header span').textContent = '+';
        });
        // Si estaba cerrado → abrirlo
        if (!isActive) {
            accordion.classList.add('active');
            icon.textContent = '−';
        }
    });
});

</script>

</body>
</html>