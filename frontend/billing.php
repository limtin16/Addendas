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
<input type="text" class="form-input <?= $hasData ? 'readonly' : '' ?>" 
       name="regime" value="<?= $data['regime'] ?? '' ?>" 
       <?= $hasData ? 'readonly' : '' ?> required>

<label>Uso de CFDI</label>
<input type="text" class="form-input <?= $hasData ? 'readonly' : '' ?>" 
       name="cfdi_use" value="<?= $data['cfdi_use'] ?? '' ?>" 
       <?= $hasData ? 'readonly' : '' ?> required>

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
function enableEdit(button) {

    document.querySelectorAll('#billingForm .form-input').forEach(el => {
        el.removeAttribute('readonly');
        el.classList.remove('readonly');
    });

    document.querySelectorAll('#billingForm input[type="checkbox"]').forEach(el => {
        el.removeAttribute('disabled');
    });

    document.getElementById('saveBtn').style.display = 'inline-block';

    button.style.display = 'none';
}
</script>

</body>
</html>