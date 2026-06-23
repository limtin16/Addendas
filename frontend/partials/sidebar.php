<?php
$isLogged = isset($_SESSION['user_id']);
// ✅ detectar base automáticamente
$script = $_SERVER['SCRIPT_NAME'];

if (strpos($script, '/addendas/') === 0) {
    $base = '/addendas';
} else {
    $base = '';
}

define('BASE_URL_sb', $base);
?>

<div class="sidebar">

    <h2 class="logo">📄 Addendas</h2>

    <?php if ($isLogged): ?>

        <div class="user">
            👤<br>
            <small><?= htmlspecialchars($_SESSION['user_email']) ?></small>
        </div>

        <!-- ✅ MENÚ PRINCIPAL -->
        <div class="menu-top">
            <a href="<?= BASE_URL_sb ?>/frontend/dashboard.php">🏠 Dashboard</a>
            <a href="<?= BASE_URL_sb ?>/frontend/buy_credits.php">💳 Comprar créditos</a>
            <a href="<?= BASE_URL_sb ?>/frontend/my_plans.php">📦 Mis Planes</a>
            <a href="<?= BASE_URL_sb ?>/frontend/billing.php">🧾 Facturación</a>
            <a href="<?= BASE_URL_sb ?>/frontend/privacy.php">📝 Política de privacidad</a>
            <a href="<?= BASE_URL_sb ?>/frontend/cancellation_policy.php">📝 Política de cancelación</a>
            <a href="<?= BASE_URL_sb ?>/frontend/account_settings.php">⚙️ Configuración</a>
            <a href="<?= BASE_URL_sb ?>/backend/public/logout.php">🚪 Salir</a>
        </div>

        <!-- ✅ AYUDA (SIEMPRE ABAJO) -->
        <div class="menu-bottom">
            <a href="<?= BASE_URL_sb ?>/frontend/help.php">❓ Ayuda</a>
        </div>

    <?php else: ?>

        <div class="menu-top">
            <a href="<?= BASE_URL_sb ?>/frontend/select_mode.php">🏠 Generar Addenda</a>
            <a href="<?= BASE_URL_sb ?>/frontend/register.php">📝 Registrarse</a>
            <a href="<?= BASE_URL_sb ?>/frontend/login.php">🔐 Login</a>
            <a href="<?= BASE_URL_sb ?>/frontend/recover_cfdi.php">📥 Recuperar CFDI</a>
            <a href="<?= BASE_URL_sb ?>/frontend/privacy.php">📝 Política de privacidad</a>
            <a href="<?= BASE_URL_sb ?>/frontend/cancellation_policy.php">📝 Política de cancelación</a>
        </div>

        <div class="menu-bottom">
            <a href="<?= BASE_URL_sb ?>/frontend/help.php">❓ Ayuda</a>
        </div>

    <?php endif; ?>

</div>