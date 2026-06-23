<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/db.php';

session_start();

$stmt = $conn->prepare("
    SELECT content, version 
    FROM policy 
    WHERE active = 1
      AND type = 'cancellation'
    ORDER BY id DESC 
    LIMIT 1
");

$stmt->execute();
$policy = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Política de Cancelación</title>

<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">

<style>

/* ✅ RESET SOLO PARA ESTA PAGE */
body {
    margin: 0;
}

/* ✅ FULL WIDTH */
.container, .main {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* ✅ LAYOUT */
.policy-page {
    display: flex;
    flex-direction: column;
    width: 100%;
    background: #f9fafb;
}

/* ✅ CONTENIDO */
.policy-container {
    max-width: 900px;
    margin: auto;
    padding: 40px 20px;
    background: #fff;
}

/* ✅ HEADER */
.policy-header {
    margin-bottom: 20px;
}

.policy-header h1 {
    margin: 0;
}

/* ✅ TEXTO */
.policy-content {
    line-height: 1.6;
}

.policy-content h2 {
    margin-top: 25px;
}

.policy-footer {
    margin-top: 30px;
    font-size: 13px;
    color: #777;
}

</style>

</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">

    <div class="policy-page">

        <div class="policy-container">

            <div class="policy-header">
                <h1>📄 Política de Cancelación y Reembolsos</h1>
                <p>Consulta las condiciones aplicables a tu compra</p>
            </div>

            <div class="policy-content">
                <?= $policy['content'] ?>
            </div>

            <div class="policy-footer">
                Versión: <?= $policy['version'] ?> |
                Última actualización: <?= date('Y-m-d') ?>
            </div>

        </div>

    </div>

</div>

</body>
</html>