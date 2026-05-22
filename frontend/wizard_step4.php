<?php
session_start();

require_once dirname(__DIR__) . '/backend/config.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

$templateId = $_GET['template_id'] ?? $_POST['template_id'] ?? null;

if (!$templateId) {
    header('Location: wizard_step1.php');
    exit;
}

$service = new TemplateService();
$template = $service->get($templateId);
if (!$template) {
    header('Location: wizard_step1.php');
    exit;
}

// Inicializar grupo activo
if (!isset($_SESSION['current_group'])) {
    $_SESSION['current_group'] = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear addenda – Paso 4</title>
<link rel="stylesheet" href="/addendas/frontend/assets/styles.css">
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">

    <div class="container">
    <div class="form-centered">
            <div class="card" data-template-id="<?= htmlspecialchars($templateId) ?>">

                <h2>Crear addenda – Paso 4</h2>
                <p>Agrega grupos repetibles y sus campos.</p>

                <?php if ($_SESSION['current_group'] === null): ?>

                <h3>Nuevo grupo</h3>

                <form method="post" action="/addendas/backend/public/start_group_step4.php">
                    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">

                    <label>Nombre del grupo</label>
                    <input type="text" name="group_name" required>

                    <label>Nombre de cada elemento</label>
                    <input type="text" name="item_name" required>

                    <button type="submit">Crear grupo</button>
                </form>

                <?php else: ?>

                <h3>Grupo: <?= htmlspecialchars($_SESSION['current_group']['name']) ?></h3>

                <p>Campos del grupo:</p>
                <ul>
                <?php foreach ($_SESSION['current_group']['children'] as $field): ?>
                    <li><?= htmlspecialchars($field['name']) ?> (<?= htmlspecialchars($field['representation']) ?>)</li>
                <?php endforeach; ?>
                </ul>

                <hr>

                <h4>Agregar campo al grupo</h4>

                <form method="post" action="/addendas/backend/public/add_field_to_group_step4.php">
                    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">

                    <label>Nombre del campo</label>
                    <input type="text" name="field_name" required>

                    <label>Representación</label>
                    <select name="representation" required>
                        <option value="attribute">Como atributo</option>
                        <option value="node">Como nodo</option>
                    </select>

                    <button type="submit">Agregar campo</button>
                </form>

                <hr>

                <form method="post" action="/addendas/backend/public/save_group_step4.php">
                    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
                    <button type="submit">Guardar grupo</button>
                </form>

                <?php endif; ?>

                <hr>

                <form method="post" action="/addendas/backend/public/save_group_step4.php">
                    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
                    <input type="hidden" name="redirect_done" value="1">
                    <button type="submit">Finalizar addenda ✅</button>
                </form>

                <hr>
            </div> <!-- fin card formulario -->
    </div>
            <!-- 🔥 PREVIEW SEPARADO -->
            <div class="preview-full">

                <div class="card">
                    <h3>👁 Vista previa de la addenda</h3>

                    <div class="preview-box">
                        <pre id="xmlStructure" class="xml-preview">Cargando…</pre>
                    </div>
                </div>

            </div>
    
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ==================================================
       PREVIEW (SIEMPRE ACTIVO)
       ================================================== */

    function decodeHtml(html) {
        var t = document.createElement('textarea');
        t.innerHTML = html;
        return t.value;
    }

    function updatePreview() {
        var structure = document.getElementById('xmlStructure');
        if (!structure) return;

        var container = document.querySelector('.card');
        if (!container) return;

        var templateId = container.dataset.templateId;
        if (!templateId) return;
        fetch(
            '/addendas/backend/public/preview_addenda_combined.php?template_id=' +
            encodeURIComponent(templateId) +
            '&_=' + Date.now()
        )
        .then(function (r) {
            return r.json();
        })
        .then(function (d) {
            if (d.structurePreview) {
             structure.textContent = decodeHtml(d.structurePreview);
            } else {
                structure.textContent = '⚠️ Sin estructura aún';
            }
        })
        .catch(function () {
            structure.textContent = 'Error cargando preview';
        });
    }

    // 🔥 SIEMPRE ejecuta el preview
    updatePreview();

    /* ==================================================
       FORMULARIO DE GRUPO (SOLO SI EXISTE)
       ================================================== */

    var groupForm = document.querySelector(
        'form[action*="add_field_to_group_step4"]'
    );

    if (!groupForm) {
        // No hay grupo activo: solo preview
        return;
    }

});
</script>

</body>
</html>
