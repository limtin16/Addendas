<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

require_once dirname(__DIR__) . '/backend/config.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

$templateId = $_GET['template_id'] ?? null;
if (!$templateId) {
    header("Location: <?= $base ?>/frontend/wizard_step1.php");
    exit;
}

$service = new TemplateService();
$template = $service->get($templateId);
if (!$template) {
    header("Location: <?= $base ?>/frontend/wizard_step1.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear addenda – Paso 3</title>
<link rel="stylesheet" href="<?= $base ?>/frontend/assets/styles.css">
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
            <form method="post" action="<?= $base ?>/backend/public/save_template_step3.php">
                <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">

                <label>Nombre del campo</label>
                <input type="text" name="field_name" placeholder="Ej. Folio" required>

                <label>Representación</label>
                <select name="representation" required>
                    <option value="node">Como nodo</option>
                    <option value="attribute">Como atributo</option>
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

            <form method="get" action="<?= $base ?>/frontend/wizard_step4.php">
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

    fetch('<?= $base ?>/backend/public/preview_addenda_combined.php?template_id=<?= urlencode($templateId) ?>')
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