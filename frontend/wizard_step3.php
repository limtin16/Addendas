<?php

require_once dirname(__DIR__) . '/backend/config.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

$templateId = $_GET['template_id'] ?? null;
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear addenda – Paso 3</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    padding: 40px;
}
.card {
    background: #fff;
    max-width: 600px;
    margin: auto;
    padding: 25px;
    border-radius: 6px;
}
label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}
input, select, button {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}
button {
    margin-top: 25px;
    cursor: pointer;
}

/* Preview */
.preview-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.preview-box {
    flex: 1 1 0;
    min-width: 0;
}
.xml-preview {
    background: #111;
    color: #6cf;
    padding: 10px;
    max-height: 300px;
    overflow: auto;
    white-space: pre-wrap;
    font-size: 13px;
}

/* UX Errors */
.ux-error {
    color: #b00020;
    font-size: 13px;
    margin-top: 4px;
    display: none;
}
.ux-error.visible {
    display: block;
}
select.error {
    border: 1px solid #b00020;
}
</style>
</head>

<body>
<div class="card">
<h2>Crear addenda – Paso 3</h2>
<p>Agrega campos simples a la addenda.</p>

<!-- ================================================= -->
<!-- FORMULARIO STEP 3 -->
<!-- ================================================= -->
<form method="post" action="/addendas/backend/public/save_template_step3.php">
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

<!-- ================================================= -->
<!-- FORMULARIO STEP 4 -->
<!-- ================================================= -->
<form method="get" action="/addendas/frontend/wizard_step4.php">
<input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
<button type="submit">Continuar al Paso 4 →</button>
</form>

<hr>

<h3>👁 Vista previa de la addenda</h3>
<div class="preview-container">
    <div class="preview-box">
        <h4>Vista previa</h4>
        <pre id="xmlStructure" class="xml-preview">Cargando…</pre>
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

    fetch('/addendas/backend/public/preview_addenda_combined.php?template_id=<?= urlencode($templateId) ?>')
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
