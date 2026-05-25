<?php
session_start();

// 🔒 si ya no existe la instancia → redirigir
if (!isset($_SESSION['addenda_instance']['structure'])) {
    header("Location: /addendas/frontend/select_mode.php");
    exit;
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
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="main">
    <div class="instance-layout">
        <div class="header">
            <h2>Rellenar Addenda Existente</h2>
            <p class="note">
                La estructura de esta addenda es fija.  
                Solo completa los valores necesarios para generar la factura final.
            </p>
        </div>
        <div class="instance-grid">
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
const isLogged = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    checkCreditsAndToggleButton();
});
const form = document.getElementById('instanceForm');
const previewBox = document.getElementById('preview');
// Controlador global para cancelar previews anteriores
let previewAbortController = null;
let userCredits = null;

// 👇 NUEVO: contador de requests
let previewRequestId = 0;

function removeNoCreditsMessage() {
    const msg = document.getElementById('noCreditsMsg');
    if (msg) msg.remove();
}

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

function checkCreditsAndToggleButton() {

    // ✅ visitante: no hay créditos
    if (!isLogged) {
        userCredits = null;
        updateGenerateButtonState();
        return;
    }

    fetch('/addendas/backend/public/get_credits.php')
        .then(r => r.json())
        .then(data => {

            if (!data.ok) {
                console.error('Error obteniendo créditos');
                return;
            }

            userCredits = data.credits;

            updateGenerateButtonState();

        })
        .catch(err => {
            console.error('Error credits:', err);
        });
}

function updateGenerateButtonState() {

    const btn = document.getElementById('generateBtn');

    // ✅ usuario con créditos
    if (isLogged && userCredits !== null && userCredits <= 0) {
        btn.disabled = true;
        showNoCreditsMessage();
        return;
    }

    // ✅ flujo normal (incluye visitante)
    if (
        !targetCfdiLoaded ||
        !previewBox.textContent.startsWith('<')
    ) {
        btn.disabled = true;
    } else {
        btn.disabled = false;
    }
}

function showNoCreditsMessage() {

    if (document.getElementById('noCreditsMsg')) return;

    const msg = document.createElement('div');

    msg.id = 'noCreditsMsg';
    msg.innerHTML = `
        ⚠️ No tienes créditos disponibles.<br>
        <a href="/addendas/frontend/buy_credits.php">Comprar créditos</a>
    `;

    msg.style.marginTop = '10px';
    msg.style.color = '#b91c1c';
    msg.style.fontSize = '14px';

    document.getElementById('generateBtn').parentElement.appendChild(msg);
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

    let data;

    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error("Respuesta inválida:", text);
        alert("Error del servidor:\n" + text);
        return;
    }

    // ✅ manejar errores correctamente
    if (!res.ok || !data.xml) {

        if (data.error === "No autorizado") {
            alert("⚠️ Tu sesión de pago ya fue utilizada.\nDebes pagar nuevamente.");

            window.location.href = "/addendas/frontend/select_mode.php";
            return;
        }

        if (data.error) {
            alert("❌ " + data.error);
            return;
        }

        alert("Error generando CFDI");
        return;
    }

    // ✅ guardar en BD
    const fdStore = new FormData();
    fdStore.append('xml', data.xml);

    const storeRes = await fetch('/addendas/backend/public/save_generated_cfdi.php', {
        method: 'POST',
        body: fdStore
    });

    const storeText = await storeRes.text();

    let saved;

    try {
        saved = JSON.parse(storeText);
    } catch (e) {
        console.error("Error guardando CFDI:", storeText);
        alert("Error guardando CFDI:\n" + storeText);
        return;
    }

    if (saved.error) {
        alert("❌ " + saved.error);
        return;
    }
    alert(`✅ CFDI generado correctamente`);
    // ✅ actualizar créditos ANTES del redirect
    checkCreditsAndToggleButton();


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
        updateGenerateButtonState();
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

        if (requestId !== previewRequestId) return;

        if (!xml || xml.startsWith('Notice') || xml.startsWith('Warning')) {
            previewBox.textContent = '❌ Error en preview:\n' + xml;
            return;
        }

        previewBox.textContent = xml;

        // ✅ habilitar botón SOLO si también hay CFDI cargado
        if (targetCfdiLoaded && xml.startsWith('<')) {
            updateGenerateButtonState();
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

// 🔥 detectar navegación desde cache (back/forward)
window.addEventListener('pageshow', function(event) {

    if (event.persisted) {
        // recarga la página completamente
        window.location.reload();
    }
});
</script>
</body>
</html>