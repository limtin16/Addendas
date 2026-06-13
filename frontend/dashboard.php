<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$dbPath = $path . "backend/db.php";
$creditServicePath = $path . "backend/src/Services/CreditService.php";
$authPath = $path . "backend/helpers/auth.php";
$path.="backend/config.php";
require_once $path;
require_once $dbPath;
require_once $creditServicePath;
require_once $authPath;

session_start();

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
<div id="predefined-warning" class="modal" style="display:none;">

    <div class="modal-content">

        <h3>⚠️ Aviso importante</h3>
        <p>
        Las addendas predefinidas son ejemplos generales y pueden no coincidir exactamente con los requisitos de tu cliente.
        </p>
        <p>
        No existe una relación directa entre esta plataforma y las empresas emisoras, por lo que las estructuras pueden variar o estar desactualizadas.
        </p>
        <div class="help-box">
        💡 Se recomienda validar con tu cliente o utilizar opciones más precisas como:
        <ul>
            <li>Subir un XML con addenda existente</li>
            <li>Subir un archivo XSD oficial</li>
        </ul>
        </div>
        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:15px;">
            <button class="btn gray" onclick="closeModal()">
                Cancelar
            </button>
            <button class="btn purple" onclick="goToPredefined()">
                Continuar
            </button>
        </div>
    </div>
</div>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="main">
    <div class="container">
        <?php if (isset($_GET['paid'])): ?>
            <div id="paid-msg" style="background:#d1fae5;padding:15px;margin-bottom:20px;border-radius:8px;">
                ✅ Pago exitoso. Tus créditos han sido agregados. Los cambios pueden tardar unos segundos en reflejarse. Si no ves el cambio, intenta recargar la página.
            </div>
        <?php endif; ?>
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
                <a href="<?= BASE_URL ?>/frontend/select_mode.php" class="btn blue" id="btn-create">
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
            <div class="card">
                <h3>📦 Addendas predefinidas</h3>
                <p>Usa plantillas ya configuradas para comenzar rápidamente</p>
                <!--<a href="<?= BASE_URL ?>/frontend/predefined_addendas.php" class="btn gray"> -->
                <a href="#" class="btn gray" id="btn-predefined">
                    Ver opciones
                </a>
            </div>
        </div>

    </div>

</div>
<script>

    const predefinedBtn = document.getElementById('btn-predefined');
    const modal = document.getElementById('predefined-warning');

    predefinedBtn.addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = 'flex';
    });

    function closeModal() {
        modal.style.display = 'none';
    }

    function goToPredefined() {
        window.location.href = "<?= BASE_URL ?>/frontend/predefined_addendas.php";
    }

    const params = new URLSearchParams(window.location.search);
    if (params.get("paid") === "1") {

        if (!sessionStorage.getItem("reload_paid")) {

            sessionStorage.setItem("reload_paid", "1");

            // ✅ pequeño delay para asegurar webhook
            setTimeout(() => {
                window.location.reload();
            }, 3000);

        } else {
            sessionStorage.removeItem("reload_paid");
        }
    }
    const userCredits = <?= (int)$credits ?>;

document.getElementById('btn-create').addEventListener('click', function(e) {

    if (userCredits <= 0) {
        e.preventDefault();

        const msg = document.createElement("div");
        msg.className = "login-error";
        msg.innerText = "❌ No tienes créditos disponibles";

        document.querySelector(".container").prepend(msg);

        setTimeout(() => {
            window.location.href = "<?= BASE_URL ?>/frontend/buy_credits.php";
        }, 3000);
    }

});
const msg = document.getElementById("paid-msg");
if (msg) {
    setTimeout(() => {
        window.location.reload();
    }, 5000); // 5 segundos
}
</script>
</body>
</html>