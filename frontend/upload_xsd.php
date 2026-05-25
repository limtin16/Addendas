<?php
// upload_xsd.php
session_start();

if (
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['guest_paid'])
) {
    header("Location: /addendas/frontend/select_mode.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir XSD de Addenda</title>
    <link rel="stylesheet" href="/addendas/frontend/assets/styles.css">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">

    <div class="container form-centered">

        <div class="card upload-card">

            <h2>📐 Generar Addenda desde XSD</h2>

            <p class="description">
                Sube el archivo XSD de la addenda.<br>
                Se generará una estructura base que podrás completar después.
            </p>

            <form action="/addendas/backend/public/analyze_xsd.php"
                method="POST"
                enctype="multipart/form-data"
                id="uploadXsdForm">

                <!-- ✅ FILE INPUT BONITO -->
                <div class="upload-area">

                    <input type="file"
                        name="xsd"
                        id="xsdFile"
                        accept=".xsd"
                        required>

                    <label for="xsdFile" class="upload-button">
                        📂 Seleccionar archivo XSD
                    </label>

                    <div id="xsdFileInfo" class="file-info">
                        Ningún archivo seleccionado
                    </div>

                    <div id="xsdError" class="error">
                        Debes seleccionar un archivo XSD válido.
                    </div>

                </div>

                <button type="submit" class="btn blue full">
                    ⚙️ Generar Addenda
                </button>

                <div class="note">
                    * Solo archivos .xsd
                </div>

            </form>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('xsdFile');
    const info = document.getElementById('xsdFileInfo');
    const error = document.getElementById('xsdError');
    const form = document.getElementById('uploadXsdForm');

    input.addEventListener('change', function () {
        error.classList.remove('visible');

        if (!input.files || !input.files.length) {
            info.textContent = 'Ningún archivo seleccionado';
            return;
        }

        const file = input.files[0];

        if (!file.name.toLowerCase().endsWith('.xsd')) {
            error.textContent = 'El archivo debe ser .xsd';
            error.classList.add('visible');
            input.value = '';
            info.textContent = 'Ningún archivo seleccionado';
            return;
        }

        info.textContent = '📄 ' + file.name;
    });

    form.addEventListener('submit', function (e) {
        if (!input.files.length) {
            error.classList.add('visible');
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>