<?php
session_start();
require_once dirname(__DIR__) . '/backend/db.php';

$isLogged = !empty($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;

if (!$isLogged) {
    header("Location: /addendas/frontend/login.php");
    exit;
}

// ✅ datos fiscales
$stmt = $conn->prepare("SELECT * FROM billing_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$data = $data ?? [];

// ✅ compras (paquetes)
$stmt = $conn->prepare("
    SELECT id, credits, created_at
    FROM user_credit_batches
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$batches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ✅ solicitudes existentes
$stmt = $conn->prepare("
    SELECT purchase_id 
    FROM invoice_requests
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$requestedIds = array_column($requests, 'purchase_id');

// ✅ detectar si ya hay datos
$hasData = !empty($data);

// ✅ traer catalogo SAT
$res = $conn->query("SELECT code, description FROM sat_regimenes ORDER BY code ASC");
$regimenes = $res->fetch_all(MYSQLI_ASSOC);

$selectedRegime = $data['regime'] ?? null;

$usos = [];

if (!empty($selectedRegime)) {
    $stmt = $conn->prepare("
        SELECT u.code, u.description
        FROM sat_uso_cfdi u
        JOIN sat_regimen_uso r ON u.code = r.uso_cfdi_code
        WHERE r.regimen_code = ?
    ");
    $stmt->bind_param("s", $selectedRegime);
    $stmt->execute();
    $usos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturación</title>
    <link rel="stylesheet" href="/addendas/frontend/assets/styles.css">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
<div class="container">

<div class="grid">

<!-- ✅ DATOS FISCALES -->
<div class="card">

<h2>🧾 Datos de Facturación</h2>

<p class="note">
Guarda tus datos fiscales para generar facturas automáticamente.
</p>

<hr>

<form method="POST" action="/addendas/backend/public/save_billing.php" id="billingForm">

<label>RFC</label>
<input type="text" class="form-input <?= $hasData ? 'readonly' : '' ?>" 
       name="rfc" value="<?= $data['rfc'] ?? '' ?>" 
       <?= $hasData ? 'readonly' : '' ?> required>

<label>Nombre / Razón Social</label>
<input type="text" class="form-input <?= $hasData ? 'readonly' : '' ?>" 
       name="name" value="<?= $data['name'] ?? '' ?>" 
       <?= $hasData ? 'readonly' : '' ?> required>

<label>Código Postal</label>
<input type="text" class="form-input <?= $hasData ? 'readonly' : '' ?>" 
       name="postal_code" value="<?= $data['postal_code'] ?? '' ?>" 
       <?= $hasData ? 'readonly' : '' ?> required>

<label>Régimen Fiscal</label>

<?php if ($hasData): ?>

    <!-- ✅ MODO BLOQUEADO (MEJOR UX) -->
    <input type="text" class="form-input readonly"
           value="<?php
               foreach ($regimenes as $r) {
                   if ($r['code'] == ($data['regime'] ?? '')) {
                       echo $r['code'] . ' - ' . $r['description'];
                   }
               }
           ?>"
           readonly>

    <!-- ✅ necesario para enviar valor -->
    <input type="hidden" name="regime" value="<?= $data['regime'] ?>">

<?php else: ?>

    <!-- ✅ SELECT EDITABLE -->
    <select name="regime" class="form-input" required>

        <option value="">Selecciona un régimen</option>

        <?php foreach ($regimenes as $r): ?>
            <option value="<?= $r['code'] ?>">
                <?= $r['code'] ?> - <?= $r['description'] ?>
            </option>
        <?php endforeach; ?>

    </select>

<?php endif; ?>

<label>Uso de CFDI</label>

<select name="cfdi_use" class="form-input"
        <?= $hasData ? 'disabled' : '' ?>
        required>

    <option value="">Selecciona un uso</option>

    <?php foreach ($usos as $u): ?>
        <option value="<?= $u['code'] ?>"
            <?= ($data['cfdi_use'] ?? '') == $u['code'] ? 'selected' : '' ?>>
            <?= $u['code'] ?> - <?= $u['description'] ?>
        </option>
    <?php endforeach; ?>

</select>

<?php if ($hasData): ?>
    <!-- ✅ ESTO VA AQUÍ -->
    <input type="hidden" name="cfdi_use" value="<?= $data['cfdi_use'] ?>">
<?php endif; ?>

<label>Email</label>
<input type="email" class="form-input <?= $hasData ? 'readonly' : '' ?>" 
       name="email" value="<?= $data['email'] ?? '' ?>" 
       <?= $hasData ? 'readonly' : '' ?> required>

<label>
    <input type="checkbox" name="auto_invoice"
        <?= !empty($data['auto_invoice']) ? 'checked' : '' ?>
        <?= $hasData ? 'disabled' : '' ?>>
    Generar facturas automáticamente
</label>

<br><br>

<?php if ($hasData): ?>

<button type="button" class="btn green" onclick="enableEdit(this)">
    ✏️ Editar datos
</button>

<button type="submit" class="btn blue" id="saveBtn" style="display:none;">
    💾 Guardar cambios
</button>

<?php else: ?>

<button class="btn blue">💾 Guardar datos</button>

<?php endif; ?>

</form>

</div>

<!-- ✅ SOLICITAR FACTURA -->
<div class="card scrollable">

<h2>📄 Solicitar Factura</h2>

<p class="note">
Puedes solicitar una factura hasta 5 días después de tu compra.  
La factura será generada en un plazo máximo de 5 días.
</p>

<hr>

<?php if ($batches): ?>

<table class="table-compact">

<tr>
    <th>ID Compra</th>
    <th>Créditos</th>
    <th>Fecha</th>
    <th></th>
</tr>

<?php foreach ($batches as $b): ?>

<tr>
    <td><?= $b['id'] ?></td>
    <td><?= $b['credits'] ?> créditos</td>
    <td><?= $b['created_at'] ?></td>
    <td>

        <?php if (in_array($b['id'], $requestedIds)): ?>

            <button class="btn small" disabled title="Factura ya solicitada">
                ✅
            </button>

        <?php else: ?>

            <form method="POST" action="/addendas/backend/public/request_invoice.php"
                  onsubmit="return confirm('¿Deseas solicitar la factura de esta compra?');">

                <input type="hidden" name="purchase_id" value="<?= $b['id'] ?>">
                <button class="btn small">🧾</button>

            </form>

        <?php endif; ?>

    </td>
</tr>

<?php endforeach; ?>

</table>

<?php else: ?>

<p>No tienes compras registradas.</p>

<?php endif; ?>

</div>

</div>
</div>
</div>
<script>

// ✅ EVENTO PARA CAMBIO DE RÉGIMEN
document.addEventListener('change', function(e) {

    if (e.target.name === 'regime') {

        const regime = e.target.value;

        fetch('/addendas/backend/public/get_usos_cfdi.php?regime=' + regime)
            .then(res => res.json())
            .then(data => {

                const select = document.querySelector('[name="cfdi_use"]');
                select.innerHTML = '<option value="">Selecciona un uso</option>';

                data.forEach(u => {
                    let opt = document.createElement('option');
                    opt.value = u.code;
                    opt.textContent = u.code + ' - ' + u.description;
                    select.appendChild(opt);
                });

            })
            .catch(err => console.error(err));
    }

});


// ✅ 🔥 ESTA ES LA PARTE QUE TE FALTABA (PASO 5)
window.addEventListener('DOMContentLoaded', function () {

    const regimeSelect = document.querySelector('[name="regime"]');

    if (regimeSelect && regimeSelect.value) {

        // 👇 dispara cambio automáticamente para llenar usos
        regimeSelect.dispatchEvent(new Event('change'));
    }

});

function enableEdit(button) {

    const form = document.getElementById("billingForm");

    // ✅ desbloquear inputs (readonly)
    form.querySelectorAll('.form-input').forEach(el => {
        el.removeAttribute('readonly');
        el.classList.remove('readonly');
    });

    // ✅ desbloquear selects (IMPORTANTE)
    form.querySelectorAll('select').forEach(el => {
        el.removeAttribute('disabled');
    });

    // ✅ buscar hidden de régimen
    const regimeHidden = form.querySelector('input[name="regime"]');

    if (regimeHidden) {

        const currentValue = regimeHidden.value;

        const select = document.createElement('select');
        select.name = 'regime';
        select.className = 'form-input';

        <?php foreach ($regimenes as $r): ?>

        var option = document.createElement("option");

        option.value = <?= json_encode($r['code']) ?>;
        option.textContent = <?= json_encode($r['code'] . ' - ' . $r['description']) ?>;

        if (option.value === currentValue) {
            option.selected = true;
        }

        select.appendChild(option);

        <?php endforeach; ?>

        // ✅ reemplazar input visible
        regimeHidden.previousElementSibling.replaceWith(select);

        // ✅ eliminar hidden
        regimeHidden.remove();
    }

    // ✅ DISPARAR carga de usos
    const newRegimeSelect = form.querySelector('[name="regime"]');

    if (newRegimeSelect) {
        newRegimeSelect.dispatchEvent(new Event('change'));
    }

    // ✅ habilitar checkbox
    form.querySelectorAll('input[type="checkbox"]').forEach(el => {
        el.removeAttribute('disabled');
    });

    // ✅ mostrar botón guardar
    document.getElementById('saveBtn').style.display = 'inline-block';

    // ✅ ocultar botón editar
    button.style.display = 'none';
}
</script>
</body>
</html>