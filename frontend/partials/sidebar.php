<?php
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
            <a href="/frontend/dashboard.php">🏠 Dashboard</a>
            <a href="/frontend/cfdi_list.php">📄 Mis CFDIs</a>
            <a href="/frontend/templates_list.php">📁 Mis Templates</a>
            <a href="/frontend/select_mode.php">➕ Crear Addenda</a>
            <a href="/frontend/buy_credits.php">💳 Comprar créditos</a>
            <a href="/frontend/my_plans.php">📦 Mis Planes</a>
            <a href="/frontend/billing.php">🧾 Facturación</a>
            <a href="/frontend/account_settings.php">⚙️ Configuración</a>
            <a href="/backend/public/logout.php">🚪 Salir</a>
        </div>

        <!-- ✅ AYUDA (SIEMPRE ABAJO) -->
        <div class="menu-bottom">
            <a href="/frontend/help.php">❓ Ayuda</a>
        </div>

    <?php else: ?>

        <div class="menu-top">
            <a href="/frontend/select_mode.php">➕ Crear Addenda</a>
            <a href="/frontend/recover_cfdi.php">📄 Recuperar CFDIs</a>
            <a href="/frontend/register.php">📝 Registrarse</a>
            <a href="/frontend/login.php">🔐 Login</a>
        </div>

        <div class="menu-bottom">
            <a href="/frontend/help.php">❓ Ayuda</a>
        </div>

    <?php endif; ?>

</div>