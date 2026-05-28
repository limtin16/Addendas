<?php
$path = "";
$depth = substr_count(__DIR__, DIRECTORY_SEPARATOR) - substr_count(__DIR__, DIRECTORY_SEPARATOR) + substr_count(substr(__DIR__, strpos(__DIR__, 'addendas')), DIRECTORY_SEPARATOR);
for ($i = 0; $i < $depth; $i++) {
    $path .= "../";
}
$path .= "backend/config.php";
require_once $path;

session_start();

require_once dirname(__DIR__) . '/backend/db.php';
require_once dirname(__DIR__) . '/backend/src/Services/CreditService.php';
require_once dirname(__DIR__) . '/backend/helpers/auth.php';


// ✅ validar sesión primero
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}

$userId = requireAuthAndPrivacy($conn);

// ✅ ahora sí usar servicios
$creditService = new CreditService($conn);
$credits = $creditService->getAvailableCredits($userId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
</head>

<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
            <?php if (isset($_GET['paid'])): ?>
                <div style="background:#d1fae5;padding:15px;margin-bottom:20px;border-radius:8px;">
                    ✅ Pago exitoso. Tus créditos han sido agregados.
                </div>
            <?php endif; ?>
<div class="main">
    <div class="container">
        <div class="welcome">
            <h2>Bienvenido 👋</h2>
            <p>Selecciona una acción para comenzar</p>
            <div class="credits-box">
                💳 Tienes <b><?= $credits ?></b> crédito<?= $credits == 1 ? '' : 's' ?> disponible<?= $credits == 1 ? '' : 's' ?>
            </div>
            <?php if ($credits <= 0): ?>
                <div class="credits-box" style="background:#fee2e2; border-color:#fecaca; color:#991b1b;">
                    ⚠️ No tienes créditos disponibles.
                    <a href="<?= BASE_URL ?>/frontend/buy_credits.php" class="btn red">
                        Comprar créditos
                    </a>
                </div>
                <?php if ($credits > 0 && $credits <= 5): ?>
                <div class="credits-box" style="background:#fff7ed; border-color:#fed7aa; color:#9a3412;">
                    🔔 Te quedan pocos créditos (<?= $credits ?>)
                </div>
            <?php endif; ?>
            <?php endif; ?>

        </div>

        <div class="cards">

            <div class="card">
                <h3>🆕 Crear Addenda</h3>
                <p>Genera una nueva addenda desde cero</p>
                <a href="<?= BASE_URL ?>/frontend/select_mode.php" class="btn blue">
                    Crear
                </a>
            </div>

            <div class="card">
                <h3>📁 Mis Templates</h3>
                <p>Reutiliza templates guardados</p>
                <a href="<?= BASE_URL ?>/frontend/templates_list.php" class="btn gray">
                    Ver templates
                </a>
            </div>

            <div class="card">
                <h3>📑 CFDIs generados</h3>
                <p>Consulta y descarga CFDIs generados</p>
                <a href="<?= BASE_URL ?>/frontend/cfdi_list.php" class="btn gray">
                    Ver historial
                </a>
            </div>

        </div>

    </div>

</div>

</body>
<script>
const params = new URLSearchParams(window.location.search);

if (params.get("paid") === "1") {

    if (!sessionStorage.getItem("reload_paid")) {

        sessionStorage.setItem("reload_paid", "1");

        // ✅ pequeño delay para asegurar webhook
        setTimeout(() => {
            window.location.reload();
        }, 800);

    } else {
        sessionStorage.removeItem("reload_paid");
    }
}
</script>
</html>