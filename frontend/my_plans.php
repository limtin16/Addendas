<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$path.="backend/config.php";
require_once $path;

session_start();

require_once dirname(__DIR__) . '/backend/db.php';
require_once dirname(__DIR__) . '/backend/helpers/auth.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}

$userId = requireAuthAndPrivacy($conn);

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

// ✅ USO RECIENTE
$logStmt = $conn->prepare("
    SELECT *
    FROM credit_usage_logs
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");

$logStmt->bind_param("i", $userId);
$logStmt->execute();
$logs = $logStmt->get_result();

// ✅ ALERTA POR EXPIRAR
$expStmt = $conn->prepare("
    SELECT SUM(remaining_credits) as credits, expires_at
    FROM user_credit_batches
    WHERE user_id = ?
      AND remaining_credits > 0
      AND expires_at > NOW()
    GROUP BY expires_at
    ORDER BY expires_at ASC
    LIMIT 1
");

$expStmt->bind_param("i", $userId);
$expStmt->execute();
$expiringSoon = $expStmt->get_result()->fetch_assoc();

$warningText = null;

if ($expiringSoon) {

    $expires = new DateTime($expiringSoon['expires_at']);
    $daysLeft = (int)$now->diff($expires)->format('%a');
    $creditsLeft = (int)$expiringSoon['credits'];

    if ($daysLeft <= 5) {
        $warningText = "⚠️ {$creditsLeft} crédito" .
            ($creditsLeft === 1 ? "" : "s") .
            " expiran en {$daysLeft} día" .
            ($daysLeft === 1 ? "" : "s");
    }
}

// ✅ preparar expirados colapsables
$lastExpired = $expiredPlans[0] ?? null;
$olderExpired = array_slice($expiredPlans, 1);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Planes</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container">

        <h2>💳 Mis Planes</h2>

        <!-- ✅ ALERTA -->
        <?php if ($warningText): ?>
            <div class="credits-warning">
                <?= $warningText ?>
            </div>
        <?php endif; ?>


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

        <?php if (!$lastExpired): ?>
            <p class="empty">No hay historial</p>
        <?php else: ?>

            <!-- ✅ último plan -->
            <div class="plan-item expired">

                <div class="plan-row">
                    <b><?= $lastExpired['credits'] ?> créditos</b>
                    <span class="badge gray">Finalizado</span>
                </div>

                <div class="plan-meta">
                    🔴 Disponibles: <?= $lastExpired['remaining_credits'] ?><br>
                    📅 Comprado: <?= $lastExpired['created_at'] ?><br>
                    ⏳ Expiró: <?= $lastExpired['expires_at'] ?>
                </div>

            </div>

            <!-- ✅ botón expandir -->
            <?php if (!empty($olderExpired)): ?>
                <button id="toggleExpired" class="btn gray small">
                    Ver historial completo
                </button>

                <div id="expiredList" style="display:none; margin-top:15px;">

                    <?php foreach ($olderExpired as $p): ?>

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

                </div>
            <?php endif; ?>

        <?php endif; ?>


        <!-- ✅ USO -->
        <h3 style="margin-top:40px;">📊 Uso reciente</h3>

        <?php if ($logs->num_rows === 0): ?>
            <p class="empty">No hay uso registrado</p>
        <?php else: ?>

            <?php while ($log = $logs->fetch_assoc()): ?>

                <div class="plan-item">

                    <div class="plan-row">
                        <b>-<?= $log['credits_used'] ?> crédito<?= $log['credits_used'] > 1 ? 's' : '' ?></b>
                        <span><?= $log['created_at'] ?></span>
                    </div>

                    <div class="plan-meta">
                        <?= htmlspecialchars($log['description']) ?>
                    </div>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>

    </div>
</div>

<script>
document.getElementById('toggleExpired')?.addEventListener('click', function () {

    const list = document.getElementById('expiredList');

    if (list.style.display === 'none') {
        list.style.display = 'block';
        this.textContent = 'Ocultar historial';
    } else {
        list.style.display = 'none';
        this.textContent = 'Ver historial completo';
    }

});
</script>

</body>
</html>