<?php
// upload_addenda.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Subir Addenda o CFDI</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    padding: 40px;
}

.container {
    max-width: 600px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 8px;
}

h1 {
    text-align: center;
}

p.description {
    text-align: center;
    color: #555;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

input[type="file"] {
    width: 100%;
}

button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    cursor: pointer;
}

.note {
    font-size: 13px;
    color: #666;
    margin-top: 10px;
}

.error {
    color: #b00020;
    font-size: 14px;
    margin-top: 10px;
    display: none;
}
.error.visible {
    display: block;
}

.file-info {
    font-size: 14px;
    margin-top: 10px;
    color: #0078d4;
}
</style>
</head>

<body>

<div class="container">

<h1>Subir Addenda o CFDI</h1>

<p class="description">
Sube un CFDI que contenga una Addenda o un archivo de Addenda XML independiente.
El sistema analizará la estructura para recrear la plantilla automáticamente.
</p>

<form method="post"
      action="/addendas/backend/public/analyze_addenda.php"
      enctype="multipart/form-data"
      id="uploadForm">

    <div class="form-group">
        <input type="file"
               name="addenda_xml"
               id="addendaXml"
               accept=".xml"
               required>
        <div class="file-info" id="fileInfo"></div>
        <div class="error" id="fileError">
            Debes seleccionar un archivo XML válido.
        </div>
    </div>

    <button type="submit">Analizar archivo</button>

    <div class="note">
        * Archivos soportados: CFDI con Addenda o Addenda XML aislada.
    </div>
</form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var input = document.getElementById('addendaXml');
    var form = document.getElementById('uploadForm');
    var errorBox = document.getElementById('fileError');
    var fileInfo = document.getElementById('fileInfo');

    input.addEventListener('change', function () {
        errorBox.classList.remove('visible');
        fileInfo.textContent = '';

        if (!input.files || !input.files.length) {
            return;
        }

        var file = input.files[0];
        var name = file.name.toLowerCase();

        if (!name.endsWith('.xml')) {
            errorBox.textContent = 'El archivo debe ser XML (.xml)';
            errorBox.classList.add('visible');
            input.value = '';
            return;
        }

        fileInfo.textContent = 'Archivo seleccionado: ' + file.name;
    });

    form.addEventListener('submit', function (e) {
        if (!input.files || !input.files.length) {
            errorBox.classList.add('visible');
            e.preventDefault();
            return;
        }
    });
});
</script>

</body>
</html>