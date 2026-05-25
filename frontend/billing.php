<?php
session_start();
require_once dirname(__DIR__) . '/backend/db.php';

$isLogged = !empty($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;

if (!$isLogged) {
    header("Location: /addendas/frontend/login.php");
    exit;
}

// ✅ cargar datos fiscales
$stmt = $conn->prepare("SELECT * FROM billing_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$data = $data ?? [];

// ✅ obtener CFDIs (compras)
$stmt = $conn->prepare("
    SELECT id, filename, created_at
    FROM generated_cfdis
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cfdis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

<!-- ✅ TARJETA 1: DATOS FISCALES -->
<div class="card">

<h2>🧾 Datos de Facturación</h2>

<p class="note">
Guarda tus datos fiscales para generar facturas automáticamente.
</p>

<hr>

<form method="POST" action="/addendas/backend/public/save_billing.php">

<label>RFC</label>
<input type="text" class="form-input" name="rfc" value="<?= $data['rfc'] ?? '' ?>" required>

<label>Nombre / Razón Social</label>
<input type="text" class="form-input" name="name" value="<?= $data['name'] ?? '' ?>" required>

<label>Código Postal</label>
<input type="text" class="form-input" name="postal_code" value="<?= $data['postal_code'] ?? '' ?>" required>

<label>Régimen Fiscal</label>
<input type="text" class="form-input" name="regime" value="<?= $data['regime'] ?? '' ?>" required>

<label>Uso de CFDI</label>
<input type="text" class="form-input" name="cfdi_use" value="<?= $data['cfdi_use'] ?? '' ?>" required>

<label>Email</label>
<input type="email" class="form-input" name="email" value="<?= $data['email'] ?? '' ?>" required>

<label>
    <input type="checkbox" name="auto_invoice" <?= !empty($data['auto_invoice']) ? 'checked' : '' ?>>
    Generar facturas automáticamente
</label>

<br><br>

<button class="btn blue">💾 Guardar datos</button>

</form>

</div>

<!-- ✅ TARJETA 2: SOLICITAR FACTURA -->
<div class="card scrollable">

<h2>📄 Solicitar Factura</h2>

<p class="note">
Puedes solicitar una factura hasta 5 días después de tu compra.  
La factura será generada en un plazo máximo de 5 días.
</p>

<hr>

<?php if ($cfdis): ?>

<table class="table-compact">

<tr>
    <th>ID</th>
    <th>Archivo</th>
    <th>Fecha</th>
    <th></th>
</tr>

<?php foreach ($cfdis as $c): ?>

<tr>
    <td><?= $c['id'] ?></td>
    <td title="<?= htmlspecialchars($c['filename']) ?>">
    <?= htmlspecialchars($c['filename']) ?></td>
    <td><?= $c['created_at'] ?></td>
    <td>

        <form method="POST" action="/addendas/backend/public/request_invoice.php">
            <input type="hidden" name="purchase_id" value="<?= $c['id'] ?>">
            <button class="btn small">🧾</button>
        </form>

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

</body>
</html>