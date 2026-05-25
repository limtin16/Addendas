<?php
session_start();

require_once dirname(__DIR__) . '/backend/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /addendas/frontend/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// ✅ obtener planes
$stmt = $conn->prepare("
    SELECT *
    FROM user_credit_batches
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$activePlans = [];
$expiredPlans = [];

$now = new DateTime();

while ($row = $result->fetch_assoc()) {

    $expires = new DateTime($row['expires_at']);

    if ($row['remaining_credits'] > 0 && $expires > $now) {
        $activePlans[] = $row;
    } else {
        $expiredPlans[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Planes</title>
<link rel="stylesheet" href="/addendas/frontend/assets/styles.css">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container">

        <h2>💳 Mis Planes</h2>

        <!-- ✅ PLANES ACTIVOS -->
        <h3 style="margin-top:30px;">✅ Activos</h3>

        <?php if (empty($activePlans)): ?>
            <p class="empty">No tienes planes activos</p>
        <?php else: ?>

            <?php foreach ($activePlans as $p): ?>

                <div class="plan-item active">

                    <div class="plan-row">
                        <b><?= $p['credits'] ?> créditos</b>
                        <span class="badge green">Activo</span>
                    </div>

                    <div class="plan-meta">
                        🟢 Disponibles: <?= $p['remaining_credits'] ?><br>
                        📅 Comprado: <?= $p['created_at'] ?><br>
                        ⏳ Expira: <?= $p['expires_at'] ?>
                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>


        <!-- ✅ PLANES EXPIRADOS -->
        <h3 style="margin-top:40px;">⛔ Expirados / Usados</h3>

        <?php if (empty($expiredPlans)): ?>
            <p class="empty">No hay historial</p>
        <?php else: ?>

            <?php foreach ($expiredPlans as $p): ?>

                <div class="plan-item expired">

                    <div class="plan-row">
                        <b><?= $p['credits'] ?> créditos</b>
                        <span class="badge gray">Finalizado</span>
                    </div>

                    <div class="plan-meta">
                        🔴 Disponibles: <?= $p['remaining_credits'] ?><br>
                        📅 Comprado: <?= $p['created_at'] ?><br>
                        ⏳ Expiró: <?= $p['expires_at'] ?>
                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</div>

</body>
</html>