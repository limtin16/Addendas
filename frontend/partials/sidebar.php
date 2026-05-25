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

        <a href="/addendas/frontend/dashboard.php">🏠 Dashboard</a>
        <a href="/addendas/frontend/cfdi_list.php">📄 Mis CFDIs</a>
        <a href="/addendas/frontend/templates_list.php">📁 Mis Templates</a>
        <a href="/addendas/frontend/select_mode.php">➕ Crear Addenda</a>
        <a href="/addendas/frontend/buy_credits.php">💳 Comprar créditos</a>
        <a href="/addendas/frontend/my_plans.php">📦 Mis Planes</a>
        <a href="/addendas/frontend/account_settings.php">⚙️ Configuración</a>
        <a href="/addendas/backend/public/logout.php">🚪 Salir</a>

    <?php else: ?>

        <a href="/addendas/frontend/select_mode.php">➕ Crear Addenda</a>
        <a href="/addendas/frontend/cfdi_list.php">📄 CFDIs</a>
        <a href="/addendas/frontend/register.php">📝 Registrarse</a>
        <a href="/addendas/frontend/login.php">🔐 Login</a>

    <?php endif; ?>

</div>