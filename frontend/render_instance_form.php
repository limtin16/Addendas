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
<link rel="stylesheet" href="/addendas/frontend/assets/styles.css">
<style>
        /* 🎯 ROOT SOLO PARA ESTA PÁGINA */
        .render-main {
            padding: 20px;
        }

        .render-container {
            max-width: 1200px;
            margin: auto;
        }

        /* ✅ HEADER */
        .render-container .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .render-container .note {
            color: #555;
            font-size: 14px;
        }

        /* ✅ GRID */
        .render-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: flex-start;
        }

        /* =======================
        FORM
        ======================= */
        .form-column fieldset {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .form-column label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 4px;
            display: block;
        }

        .form-column input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        /* =======================
        PREVIEW
        ======================= */
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
        BOTÓN
        ======================= */
        #generateBtn {
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

        #generateBtn:hover {
            background: #0053a0;
        }

        #generateBtn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: flex-start;
        }

        /* =======================
        PREVIEW EXPANDIBLE
        ======================= */
        .preview-wrapper {
            position: relative;
        }

        .preview-expand {
            position: absolute;
            top: 6px;
            right: 6px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 6px 8px;
            cursor: pointer;
        }

        .preview-wrapper.fullscreen {
            position: fixed;
            inset: 0;
            background: #111;
            z-index: 9999;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .preview-wrapper.fullscreen .preview {
            flex: 1;
            max-height: none;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="render-main">
    <div class="render-container">
        <div class="header">
            <h2>Rellenar Addenda Existente</h2>
            <p class="note">
                La estructura de esta addenda es fija.  
                Solo completa los valores necesarios para generar la factura final.
            </p>
        </div>
        <div class="render-grid">
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
                        Esta factura no debe contener Addenda.</p>
                    <input type="file"
                        id="targetCfdi"
                        accept=".xml">
                    <div class="file-status" id="targetCfdiStatus">No se ha seleccionado ningún archivo.</div>
                </div>
                <!-- PREVIEW -->
                <div class="preview-wrapper" id="previewWrapper">
                    <button class="preview-expand" id="togglePreview" title="Expandir / Contraer"> ⛶</button>
                    <h3 style="margin-top:30px;">👁 Vista previa de la Addenda</h3>
                    <pre id="preview" class="preview">Cargando…</pre>
                </div>
                <!-- BOTÓN FINAL -->
                <button id="generateBtn" disabled>Generar CFDI con Addenda</button>
            </div>

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
        const key = input.dataset.field;

        if (typeof key === "string" && key.length > 0) {
            values[key] = {
                value: input.value,
                source: input.getAttribute('data-source')
            };
        }
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

    if (!xmlAddenda || !xmlAddenda.startsWith('<')) {
        alert('El preview no es válido.');
        return;
    }

    const targetFile = targetCfdiInput.files[0];

    const formData = new FormData();
    formData.append('addenda_xml', xmlAddenda);
    formData.append('cfdi', targetFile);

    // ✅ generar CFDI
    const res = await fetch('/addendas/backend/public/generate_cfdi_raw.php', {
        method: 'POST',
        body: formData
    });

    const text = await res.text();

    console.log("RAW RESPONSE:", text);

    let data;

    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error("Respuesta inválida:", text);
        alert("Error del servidor:\n" + text);
        return;
    }

    if (!res.ok || !data.xml) {
        alert('Error generando CFDI');
        return;
    }

    // ✅ guardar en BD
    const fdStore = new FormData();
    fdStore.append('xml', data.xml);

    const storeRes = await fetch('/addendas/backend/public/save_generated_cfdi.php', {
        method: 'POST',
        body: fdStore
    });

    const saved = await storeRes.json();

    // ✅ redirect final
    window.location.href = '/addendas/frontend/cfdi_success.php?id=' + saved.id;
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
    if (targetCfdiLoaded && previewBox.textContent.startsWith('<')) {
        generateBtn.disabled = false;
    }

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

    const requestId = ++previewRequestId;

    if (previewAbortController) {
        previewAbortController.abort();
    }

    previewAbortController = new AbortController();

    fetch('/addendas/backend/public/preview_addenda.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(getValues()),
        signal: previewAbortController.signal
    })
    .then(response => response.text())
    .then(xml => {

        console.log("PREVIEW:", xml);

        if (requestId !== previewRequestId) return;

        if (!xml || xml.startsWith('Notice') || xml.startsWith('Warning')) {
            previewBox.textContent = '❌ Error en preview:\n' + xml;
            return;
        }

        previewBox.textContent = xml;

        // ✅ habilitar botón SOLO si también hay CFDI cargado
        if (targetCfdiLoaded && xml.startsWith('<')) {
            generateBtn.disabled = false;
        }

    })
    .catch(error => {
        if (error.name !== 'AbortError') {
            console.error('Preview error:', error);
            previewBox.textContent = '❌ Error cargando preview';
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