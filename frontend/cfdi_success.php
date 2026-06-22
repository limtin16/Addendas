<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
    $path.="../";
}

require_once $path . "backend/config.php";
require_once $path . "backend/db.php";

session_start();

// ✅ detectar tipo usuario
$isLoggedUser = !empty($_SESSION['user_id']);
$isGuest = empty($_SESSION['user_id']) && !empty($_SESSION['guest_paid']);

$id = $_GET['id'] ?? 0;
$templateId = $_GET['template_id'] ?? null;

// ✅ obtener CFDI
if ($isLoggedUser) {
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT id, filename, token, created_at 
        FROM generated_cfdis 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $id, $userId);

} else {
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
    <div class="container form-centered">

        <div class="card">
            <h2>✅ CFDI generado con éxito</h2>

            <p>Tu CFDI fue generado correctamente.</p>

            <div class="plan-summary">
                <b>ID:</b> <?= $cfdi['id'] ?><br>
                <b>Archivo:</b> <?= htmlspecialchars($cfdi['filename']) ?><br>
                <b>Fecha:</b> <?= $cfdi['created_at'] ?>
            </div>

            <br>

            <!-- ✅ BOTÓN DESCARGA -->
            <?php if ($isGuest): ?>
                <button class="btn blue full" onclick="handleGuestDownload(<?= $cfdi['id'] ?>)">
                    ⬇ Descargar CFDI
                </button>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/backend/public/download_cfdi_by_id.php?id=<?php echo $cfdi['id'] ?>" class="btn blue">
                   ⬇ Descargar CFDI
                </a>
            <?php endif; ?>

            <br>

            <!-- ✅ PDF -->
            <a href="<?= BASE_URL ?>/backend/public/generate_cfdi_pdf.php?id=<?= $cfdi['id'] ?>" class="btn purple"
                onclick="return confirmPDF();">
               🧾 Descargar PDF
            </a>

            <!-- ✅ SOLO GUEST: correo -->
            <?php if ($isGuest): ?>
                <div style="margin-top:20px;">
                    <label><b>📧 Recibir por correo:</b></label><br>

                    <input type="email" id="guestEmail" placeholder="tu@email.com" class="input">

                    <button class="btn gray full" style="margin-top:10px;" onclick="sendGuestEmail(<?= $cfdi['id'] ?>)">
                        📩 Enviarme el CFDI por correo
                    </button>

                    <p style="font-size:12px; color:#666; margin-top:8px;">
                        Te enviaremos un enlace para descargar tu CFDI. El enlace estará disponible durante <b>7 días</b>.
                    </p>
                </div>
            <?php endif; ?>

            <br>
            <br>
            <br>

            <!-- ✅ SOLO USER: template -->
            <?php if ($isLoggedUser && empty($_SESSION['using_template'])): ?>
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

            <br>

            <!-- ✅ BOTÓN REGRESO -->
            <?php if ($isLoggedUser): ?>
                <a href="<?= BASE_URL ?>/frontend/dashboard.php">
                    ➡ Volver al dashboard
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php">
                    ➡ Volver a la página principal
                </a>
            <?php endif; ?>

        </div>

    </div>
</div>

<!-- ✅ JS GUEST -->
<?php if ($isGuest): ?>
<script>
function handleGuestDownload(cfdiId) {

    const email = document.getElementById("guestEmail")?.value || '';

    fetch("<?= BASE_URL ?>/frontend/complete_guest_process.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            cfdi_id: cfdiId,
            email: email
        })
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) {
            alert("Error procesando el CFDI");
            return;
        }

        // ✅ descarga
        window.location.href = "<?= BASE_URL ?>/backend/public/download_cfdi_by_id.php?id=" + cfdiId;
    })
    .catch(err => {
        console.error(err);
        alert("Error inesperado");
    });
}

function sendGuestEmail(cfdiId) {

    const email = document.getElementById("guestEmail")?.value || '';

    if (!email) {
        alert("Por favor ingresa un correo válido");
        return;
    }

    fetch("<?= BASE_URL ?>/frontend/send_guest_cfdi_email.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            cfdi_id: cfdiId,
            email: email
        })
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) {
            alert("Error enviando el correo");
            return;
        }

        alert("✅ CFDI enviado correctamente a tu correo");

    })
    .catch(err => {
        console.error(err);
        alert("Error inesperado");
    });
}
</script>
<?php endif; ?>

</body>
</html>