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
$path.="backend/config.php";
require_once $path;
require_once $dbPath;
require_once $creditServicePath;

session_start();

// ✅ validar sesión
if (!isset($_SESSION['user_id'])) {
    //si necesito setear algo en caso de ser guest
}else{

$userId = $_SESSION['user_id'];

// ✅ verificar créditos
$creditService = new CreditService($conn);
$credits = $creditService->getAvailableCredits($userId);

if ($credits <= 0) {
    echo "<script>
        alert('❌ No tienes créditos disponibles');
        window.location.href = '" . BASE_URL . "/frontend/buy_credits.php';
    </script>";
    exit;
}
unset($_SESSION['using_template']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Addenda – Seleccionar modo</title>

<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">

</head>

<body>

<!-- ✅ SIDEBAR -->
<?php include __DIR__ . '/partials/sidebar.php'; ?>

<!-- ✅ CONTENIDO -->
<div class="main">

    <div class="container">

        <h2>¿Cómo deseas crear la addenda?</h2>

        <div class="mode-grid">

            <!-- MANUAL -->
            <div class="mode-card" onclick="goManual()">
                <div class="mode-icon">🛠</div>
                <div class="mode-title">Crear manualmente</div>
                <div class="mode-desc">
                    Usa el wizard paso a paso para definir la addenda desde cero.
                </div>
            </div>

            <!-- SUBIR XML -->
            <div class="mode-card" onclick="goUpload()">
                <div class="mode-icon">📄</div>
                <div class="mode-title">Subir addenda o CFDI</div>
                <div class="mode-desc">
                    Analiza una addenda existente y recrea la plantilla automáticamente.
                </div>
            </div>

            <!-- XSD -->
            <div class="mode-card" onclick="goXsd()">
                <div class="mode-icon">📐</div>
                <div class="mode-title">Subir XSD</div>
                <div class="mode-desc">
                    Genera la addenda automáticamente a partir de un archivo XSD.
                </div>
            </div>

        </div>

    </div>

</div>
<script>
const isLogged = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>
<script>
    function goWithPayment(destination) {

    if (isLogged) {
        window.location.href = destination;
        return;
    }

    // 🚀 redirige a checkout con destino
    const url = '<?= BASE_URL ?>/frontend/guest_checkout.php?redirect=' + encodeURIComponent(destination);

    window.location.href = url;
}
function goManual() {
    goWithPayment('<?= BASE_URL ?>/frontend/wizard_step1.php');
}

function goUpload() {
    goWithPayment('<?= BASE_URL ?>/frontend/upload_addenda.php');
}

function goXsd() {
    goWithPayment('<?= BASE_URL ?>/frontend/upload_xsd.php');
}
</script>

</body>
</html>