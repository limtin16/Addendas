<?php
session_start();
require_once dirname(__DIR__) . '/backend/config.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

$templateId = $_GET['template_id'] ?? null;

$service = new TemplateService();
$template = $service->get($templateId);

if (!$templateId) {
    header("Location: " . BASE_URL . "/frontend/wizard_step1.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear addenda – Paso 3</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">

    <div class="container">
    <div class="form-centered">
        <div class="card">

            <h2>Crear addenda – Paso 3</h2>
            <p>Agrega campos simples a la addenda.</p>

            <!-- FORM STEP 3 -->
            <form method="post" action="<?= BASE_URL ?>/backend/public/save_template_step3.php">
                <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">

                <label>Nombre del campo</label>
                <input type="text" name="field_name" placeholder="Ej. Folio" required>

                <label>Representación</label>
                <select name="representation" required>
                    <option selected="selected" value="attribute">Como atributo</option>
                    <option value="node">Como nodo</option>
                </select>

                <button type="submit">Agregar campo</button>
            </form>

            <hr>

            <h3>Campos agregados</h3>
            <ul>
            <?php foreach ($template->structure['root']['children'] as $node): ?>
            <?php if ($node['type'] === 'field'): ?>
            <li><?= htmlspecialchars($node['name']) ?> (<?= htmlspecialchars($node['representation']) ?>)</li>
            <?php endif; ?>
            <?php endforeach; ?>
            </ul>

            <hr>

            <form method="get" action="<?= BASE_URL ?>/frontend/wizard_step4.php">
                <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
                <button type="submit">Continuar al Paso 4 →</button>
            </form>

        </div>
    </div>
        <!-- 🔥 PREVIEW COMO TARJETA SEPARADA -->
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

    const structure = document.getElementById('xmlStructure');

    if (!structure) return;

    function decode(html) {
        const t = document.createElement('textarea');
        t.innerHTML = html;
        return t.value;
    }

    fetch('<?= BASE_URL ?>/backend/public/preview_addenda_combined.php?template_id=<?= urlencode($templateId) ?>')
    .then(function (r) {
        return r.text(); // 👈 importante para debug
    })
    .then(function (text) {

        try {
            const d = JSON.parse(text);

            if (d.structurePreview) {
                structure.textContent = decode(d.structurePreview);
            } else {
                structure.textContent = '⚠️ Sin estructura aún';
            }

        } catch (e) {
            structure.textContent = '❌ Error en preview:\n' + text;
            console.error('Preview JSON error:', e);
        }

    })
    .catch(function (error) {
        structure.textContent = '❌ Error cargando preview';
        console.error(error);
    });

});
</script>

</body>
</html>