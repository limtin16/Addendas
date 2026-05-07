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
    max-width: 900px;
    margin: auto;
    padding: 25px;
    border-radius: 8px;
}

.preview {
    background: #111;
    color: #9ef;
    padding: 15px;
    border-radius: 6px;
    height: 350px;
    overflow: auto;
    font-family: monospace;
    font-size: 13px;
}

button {
    margin-top: 15px;
    padding: 12px;
    width: 100%;
    font-size: 16px;
    cursor: pointer;
}

hr {
    margin: 30px 0;
}

.file-status {
    font-size: 13px;
    margin-top: 8px;
    color: #333;
}
</style>

</head>
<body>

<div class="card">

<h2>✅ Addenda creada correctamente</h2>

<p>Template ID:</p>
<code><?= htmlspecialchars($templateId) ?></code>

<hr>

<!-- =============================== -->
<!-- SUBIR CFDI (PRIMERO) -->
<!-- =============================== -->

<h3>📄 Generar CFDI con esta Addenda</h3>

<input type="file"
       id="targetCfdi"
       accept=".xml">

<div class="file-status" id="fileStatus">
    Ningún archivo seleccionado
</div>

<button id="generateBtn" disabled>
    Generar CFDI con Addenda ✅
</button>

<hr>

<!-- =============================== -->
<!-- PREVIEW REAL -->
<!-- =============================== -->

<h3>👁 Vista previa REAL de la Addenda</h3>

<pre id="preview" class="preview">
Sube un CFDI para ver la preview con datos reales...
</pre>

<hr>

<a href="/addendas/frontend/wizard_step1.html">
➕ Crear nueva addenda
</a>

</div>

<script>

const previewBox = document.getElementById('preview');
const fileInput = document.getElementById('targetCfdi');
const status = document.getElementById('fileStatus');
const generateBtn = document.getElementById('generateBtn');

let targetCfdiLoaded = false;

// ===============================
// SUBIR CFDI
// ===============================
fileInput.addEventListener('change', function () {

    if (!fileInput.files.length) {
        status.textContent = 'Ningún archivo seleccionado';
        generateBtn.disabled = true;
        targetCfdiLoaded = false;
        return;
    }

    const file = fileInput.files[0];

    if (!file.name.toLowerCase().endsWith('.xml')) {
        status.textContent = 'El archivo debe ser XML (.xml)';
        fileInput.value = '';
        generateBtn.disabled = true;
        targetCfdiLoaded = false;
        return;
    }

    status.textContent = 'Archivo cargado: ' + file.name;

    const formData = new FormData();
    formData.append('target_cfdi', file);

    // ✅ Guardar CFDI en sesión
    fetch('/addendas/backend/public/load_target_cfdi.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {

        if (!res.ok) {
            alert('❌ No se pudo cargar el CFDI');
            generateBtn.disabled = true;
            return;
        }

        targetCfdiLoaded = true;
        generateBtn.disabled = false;

        updatePreview();
    });
});

// ===============================
// PREVIEW REAL (con autofill)
// ===============================
function updatePreview() {

    if (!targetCfdiLoaded) {
        previewBox.textContent = 'Sube un CFDI para ver valores reales';
        return;
    }

    fetch('/addendas/backend/public/preview_addenda.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(r => r.text())
    .then(xml => {
        previewBox.textContent = xml;
    })
    .catch(error => {
        previewBox.textContent = 'Error generando preview';
        console.error(error);
    });
}

// ===============================
// GENERAR CFDI FINAL
// ===============================
generateBtn.addEventListener('click', function () {

    if (!targetCfdiLoaded) {
        alert('Debes subir primero un CFDI');
        return;
    }

    let xmlAddenda = previewBox.textContent;

    // ✅ eliminar wrapper cfdi:Addenda
    xmlAddenda = xmlAddenda.replace(/<\/?cfdi:Addenda[^>]*>/g, '').trim();

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/addendas/backend/public/generate_final_cfdi.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'addenda_xml';
    input.value = xmlAddenda;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
});

</script>

</body>
</html>