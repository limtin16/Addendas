<?php
session_start();
require_once dirname(__DIR__) . '/backend/db.php';

// ✅ Validar login
if (!isset($_SESSION['user_id'])) {
    header("Location: /addendas/frontend/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    die("❌ ID no proporcionado");
}

// ✅ Obtener CFDI
$stmt = $conn->prepare("
    SELECT id, filename, token, created_at
    FROM generated_cfdis
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();
$result = $stmt->get_result();
$cfdi = $result->fetch_assoc();

if (!$cfdi) {
    die("❌ CFDI no encontrado o no autorizado");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CFDI generado</title>
    <link rel="stylesheet" href="/addendas/frontend/assets/styles.css">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container success-container">

        <div class="card success-box">

            <h2>✅ CFDI generado con éxito</h2>

            <p class="note">
                Tu CFDI fue generado correctamente.  
                Puedes descargarlo o guardarlo para uso posterior.
            </p>

            <hr>

            <p><b>ID:</b> <?= $cfdi['id'] ?></p>
            <p><b>Archivo:</b> <?= htmlspecialchars($cfdi['filename']) ?></p>
            <p><b>Fecha:</b> <?= $cfdi['created_at'] ?></p>

            <br>

            <a href="/addendas/backend/public/download_cfdi_by_id.php?id=<?= $cfdi['id'] ?>" class="btn blue">
                ⬇ Descargar CFDI
            </a>

            <br><br>

            <a href="/addendas/frontend/dashboard.php" class="btn green">
                ➡ Volver al dashboard
            </a>

        </div>

    </div>
</div>

</body>
</html>