<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$isLogged = isset($_SESSION['user_id']);
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
            <a href="<?= $base ?>/frontend/dashboard.php">🏠 Dashboard</a>
            <a href="<?= $base ?>/frontend/cfdi_list.php">📄 Mis CFDIs</a>
            <a href="<?= $base ?>/frontend/templates_list.php">📁 Mis Templates</a>
            <a href="<?= $base ?>/frontend/select_mode.php">➕ Crear Addenda</a>
            <a href="<?= $base ?>/frontend/buy_credits.php">💳 Comprar créditos</a>
            <a href="<?= $base ?>/frontend/my_plans.php">📦 Mis Planes</a>
            <a href="<?= $base ?>/frontend/billing.php">🧾 Facturación</a>
            <a href="<?= $base ?>/frontend/account_settings.php">⚙️ Configuración</a>
            <a href="<?= $base ?>/backend/public/logout.php">🚪 Salir</a>
        </div>

        <!-- ✅ AYUDA (SIEMPRE ABAJO) -->
        <div class="menu-bottom">
            <a href="<?= $base ?>/frontend/help.php">❓ Ayuda</a>
        </div>

    <?php else: ?>

        <div class="menu-top">
            <a href="<?= $base ?>/frontend/select_mode.php">➕ Crear Addenda</a>
            <a href="<?= $base ?>/frontend/recover_cfdi.php">📄 Recuperar CFDIs</a>
            <a href="<?= $base ?>/frontend/register.php">📝 Registrarse</a>
            <a href="<?= $base ?>/frontend/login.php">🔐 Login</a>
        </div>

        <div class="menu-bottom">
            <a href="<?= $base ?>/frontend/help.php">❓ Ayuda</a>
        </div>

    <?php endif; ?>

</div>