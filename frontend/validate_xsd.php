<?php
session_start();
while (ob_get_level()) ob_end_clean();

$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
    $path.="../";
}
$path.="backend/config.php";
include $path;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Validador de Addenda</title>

<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">

<style>
body { font-family: Arial; }

.container { max-width: 800px; margin: auto; padding: 20px; }

.card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.upload-box {
    border: 2px dashed #ccc;
    padding: 20px;
    text-align: center;
    border-radius: 6px;
    margin-bottom: 15px;
    cursor: pointer;
}
.upload-box:hover { border-color: #0067c0; }

input[type="file"] { display: none; }

button {
    background: #0067c0;
    color: #fff;
    border: none;
    padding: 12px;
    width: 100%;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
}
button:disabled { background: #ccc; }

.result { margin-top: 20px; padding: 15px; border-radius: 6px; }

.success { background: #e8f8f0; border: 1px solid #39c172; color: #0f5132; }
.error { background: #fdecea; border: 1px solid #e3342f; color: #842029; }
.warning { background: #fff8e5; border: 1px solid #ffa500; }

.loader {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ccc;
    border-top: 2px solid #0067c0;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-right: 8px;
}
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>

</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="container">
<div class="card">

    <h2>✔ Validador de Addenda</h2>

    <!-- XML -->
    <div class="upload-box" onclick="xmlInput.click()">
        📄 Seleccionar XML
        <div id="xmlName"></div>
    </div>
    <input type="file" id="xmlInput" accept=".xml">

    <!-- ACCIONES -->
    <div id="actions" style="display:none;">
        <button id="validateXmlBtn">✅ Validar estructura XML</button>

        <hr>

        <div class="upload-box" onclick="xsdInput.click()">
            🧾 Seleccionar XSD
            <div id="xsdName"></div>
        </div>
        <input type="file" id="xsdInput" accept=".xsd">

        <button id="validateXsdBtn" disabled>
            🔍 Validar Addenda contra XSD
        </button>
    </div>

    <div id="resultBox"></div>

</div>
</div>

<script>
const xmlInput = document.getElementById('xmlInput');
const xsdInput = document.getElementById('xsdInput');

const xmlName = document.getElementById('xmlName');
const xsdName = document.getElementById('xsdName');

const resultBox = document.getElementById('resultBox');
const actions = document.getElementById('actions');

const validateXmlBtn = document.getElementById('validateXmlBtn');
const validateXsdBtn = document.getElementById('validateXsdBtn');

let xmlFile = null;
let xsdFile = null;

/* =========================
   ✅ SUBIR XML (FIX REAL)
========================= */

xmlInput.addEventListener('change', async () => {

    xmlFile = xmlInput.files[0];
    xmlName.textContent = xmlFile ? xmlFile.name : '';

    // reset UI SIEMPRE
    actions.style.display = 'none';
    validateXsdBtn.disabled = true;
    resultBox.innerHTML = '';

    if (!xmlFile) return;

    resultBox.className = 'result warning';
    resultBox.innerHTML = '<span class="loader"></span> Analizando XML...';

    try {
        const fd = new FormData();
        fd.append('xml', xmlFile);

        const res = await fetch('../backend/public/validate_xml_structure.php', {
            method: 'POST',
            body: fd
        });

        let text = await res.text();
        text = text.trim();

        console.log("RESP:", text);

        let data = JSON.parse(text);

        // ✅ ERROR (SIEMPRE entra aquí correctamente)
        if (data.error) {
             console.log("ENTRÓ AL ERROR");
            resultBox.className = 'result error';
            resultBox.innerHTML = `❌ ${data.error}`;

            resultBox.style.display = 'block';
            resultBox.style.visibility = 'visible';
            resultBox.style.opacity = '1';
            resultBox.style.border = '3px solid red';
            return; // 🔥 IMPORTANTE
        }

        // ✅ OK
        resultBox.className = 'result success';
        resultBox.innerHTML = '✅ XML correcto y contiene Addenda';

        actions.style.display = 'block';

    } catch (e) {

        console.error(e);

        resultBox.className = 'result error';
        resultBox.innerHTML = '❌ Error analizando XML';
    }
});

/* =========================
   ✅ SUBIR XSD
========================= */

xsdInput.addEventListener('change', () => {
    xsdFile = xsdInput.files[0];
    xsdName.textContent = xsdFile ? xsdFile.name : '';

    validateXsdBtn.disabled = !(xmlFile && xsdFile);
});

/* =========================
   ✅ VALIDAR XML
========================= */

validateXmlBtn.addEventListener('click', async () => {

    if (!xmlFile) return;

    resultBox.className = 'result warning';
    resultBox.innerHTML = '<span class="loader"></span> Validando XML...';

    try {
        const fd = new FormData();
        fd.append('xml', xmlFile);

        const res = await fetch('../backend/public/validate_xml_structure.php', {
            method: 'POST',
            body: fd
        });

        const data = await res.json();

        if (data.error) {
            resultBox.className = 'result error';
            resultBox.innerHTML = `❌ ${data.error}`;
            return;
        }

        resultBox.className = 'result success';
        resultBox.innerHTML = '✅ XML bien formado';

    } catch {
        resultBox.className = 'result error';
        resultBox.innerHTML = '❌ Error validando XML';
    }
});

/* =========================
   ✅ VALIDAR XSD
========================= */

validateXsdBtn.addEventListener('click', async () => {

    if (!xmlFile || !xsdFile) return;

    resultBox.className = 'result warning';
    resultBox.innerHTML = '<span class="loader"></span> Validando Addenda...';

    try {
        const fd = new FormData();
        fd.append('xml', xmlFile);
        fd.append('xsd', xsdFile);

        const res = await fetch('../backend/public/validate_xsd.php', {
            method: 'POST',
            body: fd
        });

        const data = await res.json();

        if (data.error) {
            resultBox.className = 'result error';
            resultBox.innerHTML = `❌ ${data.error}`;
            return;
        }

        if (data.valid) {
            resultBox.className = 'result success';
            resultBox.innerHTML = '✅ Addenda válida';
            return;
        }

        let html = '<strong>❌ Addenda inválida</strong><ul>';

        data.errors.forEach(e => {
            html += `<li>${e.message}</li>`;
        });

        html += '</ul>';

        resultBox.className = 'result error';
        resultBox.innerHTML = html;

    } catch {
        resultBox.className = 'result error';
        resultBox.innerHTML = '❌ Error validando';
    }
});
</script>

</body>
</html>