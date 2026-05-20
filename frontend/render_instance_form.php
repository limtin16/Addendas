<?php
session_start();

if (!isset($_SESSION['addenda_instance']['structure'])) {
    die('❌ No hay una addenda cargada para instanciar.');
}

$structure = $_SESSION['addenda_instance']['structure'];

function renderFields(array $nodes, string $prefix = ''): void
{
    foreach ($nodes as $node) {

        if (!is_array($node)) continue;

        $type = $node['type'] ?? '';

        // =========================
        // ✅ NODO CONTENEDOR
        // =========================
        if ($type === 'node') {

            echo "<fieldset>";
            echo "<legend>" . htmlspecialchars($node['name']) . "</legend>";

            $newPrefix = $prefix
                ? $prefix . '.' . $node['name']
                : $node['name'];

            renderFields($node['children'] ?? [], $newPrefix);

            echo "</fieldset>";
        }

        // =========================
        // ✅ GROUP (🔥 NUEVO)
        // =========================
        elseif ($type === 'group') {

            echo "<fieldset>";
            echo "<legend>Grupo: " . htmlspecialchars($node['name']) . "</legend>";

            $groupPrefix = $prefix
                ? $prefix . '.' . $node['name']
                : $node['name'];

            // ✅ item
            echo "<div style='padding:10px; border:1px dashed #ccc;'>";
            echo "<strong>" . htmlspecialchars($node['item_name'] ?? 'Item') . "</strong>";
            // ✅ NO agregar item_name al path
            $itemPrefix = $groupPrefix . '.' . ($node['item_name'] ?? 'Item');
            renderFields($node['children'] ?? [], $itemPrefix);
            echo "</div>";
            echo "</fieldset>";
        }

        // =========================
        // ✅ FIELD
        // =========================
        elseif ($type === 'field') {

            if (!isset($node['name'])) continue;

            $fieldKey = $prefix
                ? $prefix . '.' . $node['name']
                : $node['name'];

            echo "<div style='margin-bottom:10px;'>";

            echo "<label>" . htmlspecialchars($node['name']) . "</label>";

            echo "<div class='field-row'>";

            // =========================
            // ✅ INPUT / SELECT
            // =========================
            if (($node['type_data'] ?? '') === 'enum') {

                echo "<select
                        class='addenda-input'
                        data-field='" . htmlspecialchars($fieldKey) . "'>";

                echo "<option value=''>-- Seleccionar --</option>";

                foreach ($node['options'] ?? [] as $opt) {
                    echo "<option value='" . htmlspecialchars($opt) . "'>
                            " . htmlspecialchars($opt) . "
                          </option>";
                }

                echo "<option value='__manual__'>Otro valor...</option>";

                echo "</select>";

            } else {

                echo "<input type='text'
                        class='addenda-input'
                        data-field='" . htmlspecialchars($fieldKey) . "'>";
            }

            // =========================
            // ✅ CFDI AUTOFILL
            // =========================
            echo "<select class='cfdi-autofill'
                    data-target='" . htmlspecialchars($fieldKey) . "'>
                    <option value=''>— Tomar de CFDI —</option>
                  </select>";

            echo "</div>"; // field-row
            echo "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Rellenar Addenda</title>

<style>
body {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    background: #f5f6f8;
    padding: 30px;
    margin: 0;
}

.container {
    max-width: 1200px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
}

.header {
    text-align: center;
    margin-bottom: 30px;
}

.note {
    color: #555;
    margin-top: 10px;
    font-size: 14px;
}

.main {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    align-items: flex-start;
}

/* =======================
   FORMULARIO
======================= */
.form-column fieldset {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

legend {
    padding: 0 8px;
    font-weight: 600;
}

label {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 4px;
    display: block;
}

input {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

/* =======================
   PREVIEW
======================= */
.preview-column h3 {
    margin-top: 0;
}

.preview {
    background: #111;
    color: #9ef;
    padding: 15px;
    border-radius: 6px;
    max-height: 500px;
    overflow: auto;
    font-family: monospace;
    font-size: 13px;
    white-space: pre-wrap;
}

/* =======================
   BOTÓN FINAL (SOLO ESTE)
======================= */
.preview-column #generateBtn {
    margin-top: 20px;
    padding: 14px;
    width: 100%;
    font-size: 16px;
    background: #0067c0;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.preview-column #generateBtn:hover {
    background: #0053a0;
}

/* Botón deshabilitado */
#generateBtn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

/* =======================
   FACTURA DESTINO
======================= */
.target-invoice {
    padding: 15px;
    border: 1px dashed #ccc;
    border-radius: 6px;
    background: #fafafa;
    margin-bottom: 20px;
}

.target-invoice input[type="file"] {
    margin-top: 10px;
}

.file-status {
    margin-top: 8px;
    font-size: 13px;
    color: #666;
}

/* =======================
   PREVIEW EXPANDIBLE
======================= */
.preview-wrapper {
    position: relative;
}

/* Botón expandir (⛶) */
.preview-expand {
    position: absolute;
    top: 6px;
    right: 6px;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 6px 8px;
    cursor: pointer;
    font-size: 14px;
    z-index: 10;
    width: auto;
    margin: 0;
}

.preview-expand:hover {
    background: rgba(0, 0, 0, 0.8);
}

/* ===== MODO PANTALLA COMPLETA ===== */
.preview-wrapper.fullscreen {
    position: fixed;
    inset: 0;
    background: #111;
    z-index: 9999;
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.preview-wrapper.fullscreen h3 {
    color: #fff;
}

.preview-wrapper.fullscreen .preview {
    flex: 1;
    max-height: none;
    font-size: 14px;
}
.field-row {
    display: flex;
    gap: 10px;
    align-items: center;
}

.field-row input {
    flex: 1;
}

.field-row select {
    width: 240px;
    font-size: 13px;
}
.autofill-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
    padding: 10px 12px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 15px;
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>Rellenar Addenda Existente</h2>
        <p class="note">
            La estructura de esta addenda es fija.  
            Solo completa los valores necesarios para generar la factura final.
        </p>
    </div>

    <div class="main">

        <!-- FORMULARIO -->
        <div class="form-column">

            <!-- WARNING AUTOFILL (ARRIBA DEL FORMULARIO) -->
            <div id="autofillWarning" class="autofill-warning" style="display:none;">
                ⚠️ Para usar el autofill debes subir primero la factura destino.
            </div>

            <form id="instanceForm">
                <?php renderFields([$structure], ''); ?>
            </form>

        </div>

        <!-- PREVIEW + BOTÓN -->
       <div class="preview-column">

    <!-- FACTURA DESTINO -->
    <div class="target-invoice">
        <h3>📄 Factura destino</h3>
        <p class="note">
            Sube la factura (CFDI) a la que se le agregará esta addenda.
            Esta factura no debe contener Addenda.
        </p>

        <input type="file"
               id="targetCfdi"
               accept=".xml">

        <div class="file-status" id="targetCfdiStatus">
            No se ha seleccionado ningún archivo.
        </div>
    </div>

    <!-- PREVIEW -->
    <div class="preview-wrapper" id="previewWrapper">

    <button class="preview-expand" id="togglePreview" title="Expandir / Contraer">
        ⛶
    </button>

    <h3 style="margin-top:30px;">👁 Vista previa de la Addenda</h3>

    <pre id="preview" class="preview">Cargando…</pre>

    </div>

    <!-- BOTÓN FINAL -->
    <button id="generateBtn" disabled>
        Generar CFDI con Addenda
    </button>
</div>

    </div>
</div>

<script>
const form = document.getElementById('instanceForm');
const previewBox = document.getElementById('preview');
// Controlador global para cancelar previews anteriores
let previewAbortController = null;

// 👇 NUEVO: contador de requests
let previewRequestId = 0;


function getValues() {
    const values = {};

    form.querySelectorAll('.addenda-input').forEach(input => {
        values[input.dataset.field] = {
            value: input.value,
            source: input.getAttribute('data-source')
        };
        console.log({
        field: input.dataset.field,
        value: input.value,
        source: input.getAttribute('data-source')
        });
    });

    return values;
}

function escapeXml(str = '') {
    return str.replace(/[<>&'"]/g, c => ({
        '<': '&lt;',
        '>': '&gt;',
        '&': '&amp;',
        '"': '&quot;',
        "'": '&apos;'
    })[c]);
}


form.addEventListener('input', updatePreview);
updatePreview();

document.getElementById('generateBtn').addEventListener('click', async function () {

    if (!targetCfdiLoaded) {
        alert('Debes subir primero la factura destino.');
        return;
    }

    const xmlAddenda = previewBox.textContent.trim();

    // 🚨 VALIDACIÓN CRÍTICA
    if (
        !xmlAddenda ||
        xmlAddenda.startsWith('❌') ||
        !xmlAddenda.startsWith('<')
    ) {
        alert('El preview no es válido. Revisa los datos antes de generar.');
        return;
    }
    const targetFile = targetCfdiInput.files[0];

    const formData = new FormData();
    formData.append('addenda_xml', xmlAddenda);
    formData.append('cfdi', targetFile);

    const res = await fetch('/addendas/backend/public/generate_final_cfdi.php', {
        method: 'POST',
        body: formData
    });

    if (!res.ok) {
        alert('Error generando CFDI');
        return;
    }

    const xml = await res.text();

    // ✅ guardar en sesión (nuevo endpoint)
    const fdStore = new FormData();
    fdStore.append('xml', xml);

    const storeRes = await fetch('/addendas/backend/public/store_generated_cfdi.php', {
        method: 'POST',
        body: fdStore
    });

    const storeText = await storeRes.text();
    console.log("STORE:", storeText);

    // ✅ redirigir a página final
    window.location.href = '/addendas/frontend/cfdi_success.php';
});

const targetCfdiInput = document.getElementById('targetCfdi');
const generateBtn = document.getElementById('generateBtn');
const statusBox = document.getElementById('targetCfdiStatus');

let targetCfdiLoaded = false;

targetCfdiInput.addEventListener('change', function () {

    if (!this.files || !this.files.length) {
        targetCfdiLoaded = false;
        generateBtn.disabled = true;
        statusBox.textContent = 'No se ha seleccionado ningún archivo.';
        return;
    }

    const file = this.files[0];

    if (!file.name.toLowerCase().endsWith('.xml')) {
        statusBox.textContent = 'El archivo debe ser XML (.xml)';
        this.value = '';
        generateBtn.disabled = true;
        targetCfdiLoaded = false;
        return;
    }

    statusBox.textContent = 'Factura cargada: ' + file.name;
    targetCfdiLoaded = true;
    generateBtn.disabled = false;

    // Enviar CFDI destino al backend para habilitar autofill
const fd = new FormData();
fd.append('target_cfdi', file);

fetch('/addendas/backend/public/load_target_cfdi.php', {
    method: 'POST',
    body: fd
})
.then(r => r.json())
.then(res => {

    if (!res.ok) {
        alert('❌ No se pudo cargar la factura destino para autofill');
        return;
    }

    // ✅ Ahora sí se puede usar autofill
    loadCfdiAutofillSuggestions();
});

});
const togglePreviewBtn = document.getElementById('togglePreview');
const previewWrapper = document.getElementById('previewWrapper');

togglePreviewBtn.addEventListener('click', function () {
    previewWrapper.classList.toggle('fullscreen');

    // Cambiar icono
    togglePreviewBtn.textContent =
        previewWrapper.classList.contains('fullscreen') ? '✕' : '⛶';
});
function populateAutofillSelectors() {
    document.querySelectorAll('.cfdi-autofill').forEach(select => {

        cfdiSuggestions.forEach(f => {
            const opt = document.createElement('option');
            opt.value = f.path;                // cfdi.xxx
            opt.textContent = f.label;         // Label humano
            opt.dataset.value = f.value;       // Valor real
            select.appendChild(opt);
        });

        select.addEventListener('change', onAutofillSelect);
    });
}
function onAutofillSelect(event) {
    const select = event.target;
    const path = select.value;

    if (!targetCfdiLoaded) {
    showAutofillWarning();
    event.target.value = '';
    return;
}

    if (!path) return;

   const input = document.querySelector(
    `.addenda-input[data-field="${select.dataset.target}"]`
    );

    const selectedOption = select.options[select.selectedIndex];

    if (!input) return;

    // ✅ NO confiar en dataset.value (puede venir vacío)
    input.value = 'AUTO';

    setTimeout(() => {
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }, 0);

    // ✅ guardar explícitamente en atributo HTML
    input.setAttribute('data-source', path);
}
function updatePreview() {

    // Incrementar ID de request (marca este request como el más reciente)
    const requestId = ++previewRequestId;

    // Cancelar request anterior si existe
    if (previewAbortController) {
        previewAbortController.abort();
    }

    // Crear nuevo controller
    previewAbortController = new AbortController();

    fetch('/addendas/backend/public/preview_addenda.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(getValues()),
        signal: previewAbortController.signal
    })
    .then(response => response.text())
    .then(xml => {

        // ✅ SOLO pintar si este request es el último
        if (requestId === previewRequestId) {
            previewBox.textContent = xml;
        }

    })
    .catch(error => {
        // Ignorar abortos intencionales
        if (error.name !== 'AbortError') {
            console.error('Preview error:', error);
        }
    });
}

let cfdiSuggestions = [];

function loadCfdiAutofillSuggestions() {

    fetch('/addendas/backend/public/cfdi_autofill_suggestions.php')
        .then(r => r.json())
        .then(data => {

            if (data.error) {
                alert(
                    '⚠️ Para usar autofill debes subir primero la factura destino.'
                );
                disableAutofill();
                return;
            }

            cfdiSuggestions = data.fields || [];
            populateAutofillSelectors();
            enableAutofill();
            hideAutofillWarning();
        });
}

function disableAutofill() {
    document.querySelectorAll('.cfdi-autofill').forEach(sel => {
        sel.disabled = true;
    });

    showAutofillWarning();
}

function enableAutofill() {
    document.querySelectorAll('.cfdi-autofill').forEach(sel => {
        sel.disabled = false;
    });
}
document.addEventListener('DOMContentLoaded', function () {
    disableAutofill();
});
function showAutofillWarning() {
    const w = document.getElementById('autofillWarning');
    if (w) w.style.display = 'block';
}

function hideAutofillWarning() {
    const w = document.getElementById('autofillWarning');
    if (w) w.style.display = 'none';
}
</script>

</body>
</html>