<?php
$path = "";
$depth = substr_count(__DIR__, DIRECTORY_SEPARATOR) - substr_count(__DIR__, DIRECTORY_SEPARATOR) + substr_count(substr(__DIR__, strpos(__DIR__, 'addendas')), DIRECTORY_SEPARATOR);
for ($i = 0; $i < $depth; $i++) {
    $path .= "../";
}
$path .= "backend/config.php";
require_once $path;

// upload_addenda.php
session_start();

if (
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['guest_paid'])
) {
    header("Location: " . BASE_URL . "/frontend/select_mode.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Subir Addenda o CFDI</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">

    <div class="container form-centered">

        <div class="card upload-card">

            <h2>📄 Subir Addenda o CFDI</h2>

            <p class="description">
                Sube un CFDI que contenga una Addenda o un archivo XML independiente.<br>
                El sistema analizará la estructura para recrear la plantilla automáticamente.
            </p>

            <form method="post"
                action="<?= BASE_URL ?>/backend/public/analyze_addenda.php"
                enctype="multipart/form-data"
                id="uploadForm">

                <!-- ✅ FILE INPUT BONITO -->
                <div class="upload-area">

                    <input type="file"
                        name="addenda_xml"
                        id="addendaXml"
                        accept=".xml"
                        required>

                    <label for="addendaXml" class="upload-button">
                        📂 Seleccionar archivo XML
                    </label>

                    <div class="file-info" id="fileInfo"></div>

                    <div class="error" id="fileError">
                        Debes seleccionar un archivo XML válido.
                    </div>

                </div>

                <!-- ✅ BOTÓN -->
                <button type="submit" class="btn blue full">
                    🔍 Analizar archivo
                </button>

                <div class="note">
                    * Archivos soportados: CFDI con Addenda o Addenda XML aislada.
                </div>

            </form>

        </div>

    </div>
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

        fileInfo.textContent = '📄 ' + file.name;
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