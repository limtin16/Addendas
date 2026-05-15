<?php
session_start();

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

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    padding: 40px;
}
.card {
    background: #fff;
    max-width: 700px;
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
    margin-top: 15px;
    cursor: pointer;
}
ul {
    margin-top: 10px;
}
hr {
    margin: 30px 0;
}

/* UX errors */
.ux-error {
    color: #b00020;
    font-size: 13px;
    display: none;
}
.ux-error.visible {
    display: block;
}
select.error {
    border: 1px solid #b00020;
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
</style>
</head>

<body>
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
        <input type="text" name="fixed_value">
    </div>

    <!-- CALCULO -->
    <div class="origin_calc" style="display:none">
        <label>Expresión de cálculo</label>
        <input type="text" name="calculation" placeholder="cantidad * valorunitario">
    </div>

    <button type="submit">Agregar campo</button>
</form>

<hr>

<form method="post" action="/addendas/backend/public/save_group_step4.php">
    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
    <button type="submit">Guardar grupo</button>
</form>

<?php endif; ?>

<hr>

<form method="get" action="/addendas/frontend/wizard_done.php">
    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
    <button type="submit">Finalizar addenda ✅</button>
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
        var simulated = document.getElementById('xmlSimulated');
        if (!structure || !simulated) return;

        var container = document.querySelector('.card');
        if (!container) return;

        var templateId = container.dataset.templateId;
        if (!templateId) return;

        fetch(
            '/addendas/backend/public/preview_addenda_combined.php?template_id=' +
            encodeURIComponent(templateId)
        )
        .then(function (r) {
            return r.json();
        })
        .then(function (d) {
            structure.textContent = decodeHtml(d.structurePreview);
            simulated.textContent = decodeHtml(d.simulatedPreview);
        })
        .catch(function () {
            structure.textContent = 'Error cargando preview';
            simulated.textContent = '';
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

    var originSelect = groupForm.querySelector('.origin_type');
    var cfdiSelect   = groupForm.querySelector('.cfdi_field');

    if (!originSelect || !cfdiSelect) {
        return;
    }

    function toggleOrigin(value) {
        var cfdi  = groupForm.querySelector('.origin_cfdi');
        var fixed = groupForm.querySelector('.origin_fixed');
        var calc  = groupForm.querySelector('.origin_calc');

        if (cfdi)  cfdi.style.display  = value === 'cfdi' ? 'block' : 'none';
        if (fixed) fixed.style.display = value === 'fixed' ? 'block' : 'none';
        if (calc)  calc.style.display  = value === 'calculation' ? 'block' : 'none';
    }

    originSelect.addEventListener('change', function () {
        toggleOrigin(this.value);
    });

    // Estado inicial
    toggleOrigin(originSelect.value);

    /* ===========================
       VALIDACIÓN CFDI
       =========================== */

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
        if (box) {
            box.classList.remove('visible');
        }
    }

    function validateCfdi() {
        if (!cfdiSelect.value) {
            showError(cfdiSelect, 'Debes seleccionar un campo del CFDI');
            return false;
        }
        clearError(cfdiSelect);
        return true;
    }

    groupForm.addEventListener('submit', function (e) {
        if (originSelect.value === 'cfdi' && !validateCfdi()) {
            e.preventDefault();
        }
    });

    /* ===========================
       CARGA DE CAMPOS CFDI
       =========================== */

    fetch('/addendas/backend/public/cfdi_fields.php')
        .then(function (r) {
            return r.json();
        })
        .then(function (data) {
            if (!data.fields) return;

            data.fields.forEach(function (field) {
                if (field.scope !== 'concept') return;

                var opt = document.createElement('option');
                opt.value = field.value;
                opt.textContent = field.label;
                cfdiSelect.appendChild(opt);
            });
        });

});
</script>

</body>
</html>
