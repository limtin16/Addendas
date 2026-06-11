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

// 🔥 BLOQUEAR CACHE DEL NAVEGADOR
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

require_once dirname(__DIR__) . '/backend/db.php';

// ✅ Validar login
$isLogged = !empty($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    $isLogged = false;
}

$isGuest = isset($_SESSION['cfdi_generated']); 

if (!$isLogged && !$isGuest) {
    header("Location: " . BASE_URL . "/frontend/select_mode.php");
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$cfdi = null;

$id = $_GET['id'] ?? null;
$templateId = $_GET['template_id'] ?? null;

if (!$id) {
    die("❌ ID no proporcionado");
}

if ($isLogged) {

    $stmt = $conn->prepare("
        SELECT id, filename, token, created_at
        FROM generated_cfdis
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $id, $userId);

    } else {

        // �� visitante → sin filtro user_id
        $stmt = $conn->prepare("
            SELECT id, filename, token, created_at
            FROM generated_cfdis
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);

    }

    $stmt->execute();
    $result = $stmt->get_result();
    $cfdi = $result->fetch_assoc();

    if (!$cfdi) {
        die("❌ CFDI no encontrado");
    }


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CFDI generado</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container success-container">

        <div class="card success-box">

            <h2>✅ CFDI generado con éxito</h2>

            <p class="note">
                Tu CFDI fue generado correctamente.  
                Puedes descargarlo o guardarlo para uso posterior.
            </p>

            <hr>

            <p><b>ID:</b> <?php echo $cfdi['id'] ?></p>
            <p><b>Archivo:</b> <?php echo htmlspecialchars($cfdi['filename']) ?></p>
            <p><b>Fecha:</b> <?php echo $cfdi['created_at'] ?></p>

            <br>

            <a href="<?= BASE_URL ?>/backend/public/download_cfdi_by_id.php?id=<?php echo $cfdi['id'] ?>" class="btn blue">
                ⬇ Descargar CFDI
            </a>

            <br><br>

            <!--
            <a href="<?= BASE_URL ?>/backend/public/generate_cfdi_pdf.php?id=<?= $cfdi['id'] ?>" class="btn purple">
                🧾 Descargar PDF
            </a>
            -->
            <br><br>

            <?php if ($isLogged): ?>

            <br><br>

        <?php if ($isLogged && empty($_SESSION['using_template'])): ?>

        <h3>¿Guardar como template?</h3>

        <form action="<?= BASE_URL ?>/backend/public/save_template_db.php" method="POST"
            onsubmit="return confirmGuardarTemplate();">
            <input type="text" name="name" placeholder="Nombre del template" required>
            <!-- ✅ enviar ID del CFDI -->
            <input type="hidden" name="cfdi_id" value="<?= $cfdi['id'] ?>">
            <input type="hidden" name="template_id" value="<?= $templateId ?>">
            <button class="btn green">💾 Guardar template</button>
        </form>
        <?php unset($_SESSION['using_template']); ?>
        <br>

        <?php endif; ?>

            <a href="<?= BASE_URL ?>/frontend/dashboard.php" class="btn green">
                ➡ Volver al dashboard
            </a>

            <?php else: ?>

            <a href="<?= BASE_URL ?>/frontend/select_mode.php" class="btn green">
                ➡ Volver a pantalla principal
            </a>

            <?php endif; ?>

        </div>

    </div>
</div>
<script>
// 🔥 bloquear navegación hacia atrás
history.pushState(null, null, location.href);
window.addEventListener('popstate', function () {
    history.pushState(null, null, location.href);
});

window.addEventListener('pageshow', function (event) {

    if (event.persisted) {
        window.location.reload();
    }

});

function confirmGuardarTemplate() {
    return confirm(
        "¿Estás seguro de guardar este template?\n\n" +
        "Si no has descargado el CFDI, podrías perderlo."
    );
}


</script>
<?php unset($_SESSION['using_template']); ?>
</body>
</html>