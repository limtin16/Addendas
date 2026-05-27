<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
require_once dirname(__DIR__) . '/backend/helpers/auth.php';


// ✅ SOLO USUARIOS LOGUEADOS
if (!isset($_SESSION['user_id'])) {
    header("Location: /frontend/login.php");
    exit;
}

$userId = requireAuthAndPrivacy($conn);

// ✅ OBTENER CFDIs DEL USUARIO
$stmt = $conn->prepare("
    SELECT id, filename, created_at 
    FROM generated_cfdis 
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$cfdis = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis CFDIs</title>
    <link rel="stylesheet" href="/frontend/assets/styles.css">
</head>

<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="main">
    <div class="container">

        <h2>📄 Mis CFDIs generados</h2>

        <?php if (empty($cfdis)): ?>
            <p class="empty">Aún no has generado CFDIs.</p>
        <?php else: ?>

            <?php foreach ($cfdis as $c): ?>
                <div class="cfdi">
                    <div class="name"><?= htmlspecialchars($c['filename']) ?></div>
                    <div class="date">Creado: <?= $c['created_at'] ?></div>

                    <a href="/backend/public/download_cfdi_by_id.php?id=<?= $c['id'] ?>" class="btn blue">
                        Descargar
                    </a>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

        <a href="/frontend/dashboard.php" class="btn back">
        ⬅ Volver
        </a>

    </div>
</div>

</body>
</html>