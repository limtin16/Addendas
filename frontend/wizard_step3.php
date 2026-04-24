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

<label>¿De dónde se obtiene el valor?</label>
<select name="origin_type" class="origin_type" required>
    <option value="">Selecciona...</option>
    <option value="cfdi">Desde el CFDI</option>
    <option value="fixed">Valor fijo</option>
    <option value="calculation">Cálculo</option>
</select>

<!-- CFDI -->
<div class="origin_cfdi" style="display:none">
    <label>Campo del CFDI</label>
    <select name="cfdi_field" class="cfdi_field">
        <option value="">Selecciona un campo del CFDI</option>
    </select>
    <div class="ux-error">Debes seleccionar un campo del CFDI</div>
</div>

<!-- VALOR FIJO -->
<div class="origin_fixed" style="display:none">
    <label>Valor fijo</label>
    <input type="text" name="fixed_value" placeholder="Ej. MXN, PZA, 1">
</div>

<!-- CALCULO -->
<div class="origin_calc" style="display:none">
    <label>Expresión de cálculo</label>
    <input type="text" name="calculation" placeholder="Ej. cantidad * valorunitario">
</div>

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
        <h4>Estructura</h4>
        <pre id="xmlStructure" class="xml-preview">Cargando…</pre>
    </div>
    <div class="preview-box">
        <h4>Autofill simulado</h4>
        <pre id="xmlSimulated" class="xml-preview">Cargando…</pre>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

var form = document.querySelector('form[action*="save_template_step3"]');
var origin = form.querySelector('.origin_type');

/* Mostrar/ocultar origen */
function toggleOrigin(val) {
    form.querySelector('.origin_cfdi').style.display = val === 'cfdi' ? 'block' : 'none';
    form.querySelector('.origin_fixed').style.display = val === 'fixed' ? 'block' : 'none';
    form.querySelector('.origin_calc').style.display = val === 'calculation' ? 'block' : 'none';
}

origin.addEventListener('change', function () {
    toggleOrigin(this.value);
});

/* UX error */
function showError(el, msg) {
    el.classList.add('error');
    var box = el.parentNode.querySelector('.ux-error');
    if (box) {
        box.textContent = msg;
        box.classList.add('visible');
    }
}
function clearError(el) {
    el.classList.remove('error');
    var box = el.parentNode.querySelector('.ux-error');
    if (box) box.classList.remove('visible');
}

/* Validación CFDI */
function validateCfdi() {
    var sel = form.querySelector('.cfdi_field');
    if (!sel.value) {
        showError(sel, 'Debes seleccionar un campo del CFDI');
        return false;
    }
    clearError(sel);
    return true;
}

/* Submit */
form.addEventListener('submit', function (e) {
    if (origin.value === 'cfdi' && !validateCfdi()) {
        e.preventDefault();
    }
});

/* Cargar campos CFDI */
fetch('/addendas/backend/public/cfdi_fields.php')
.then(r => r.json())
.then(data => {
    if (!data.fields) return;
    var select = document.querySelector('.cfdi_field');
    data.fields
        .filter(f => f.scope === 'root')
        .forEach(f => {
            var opt = document.createElement('option');
            opt.value = f.value;
            opt.textContent = f.label;
            select.appendChild(opt);
        });
});

/* Preview */
function decode(html) {
    var t = document.createElement('textarea');
    t.innerHTML = html;
    return t.value;
}

fetch('/addendas/backend/public/preview_addenda_combined.php?template_id=<?= urlencode($templateId) ?>')
.then(r => r.json())
.then(d => {
    document.getElementById('xmlStructure').textContent = decode(d.structurePreview);
    document.getElementById('xmlSimulated').textContent = decode(d.simulatedPreview);
});
});
</script>

</body>
</html>