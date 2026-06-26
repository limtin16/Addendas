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

$idsParam = $_GET['ids'] ?? '';
$ids = array_filter(explode(',', $idsParam));

$isMultiple = count($ids) > 1;

if (empty($ids)) {
    die("❌ No se recibieron CFDIs");
}

$templateId = $_GET['template_id'] ?? null;
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
// ✅ obtener CFDI
if ($isLoggedUser) {

    $userId = $_SESSION['user_id'];

    $query = "
        SELECT id, filename, token, created_at 
        FROM generated_cfdis 
        WHERE id IN ($placeholders) AND user_id = ?
    ";

    $stmt = $conn->prepare($query);

    $types .= 'i';
    $params = array_merge($ids, [$userId]);

    $stmt->bind_param($types, ...$params);

} else {

    $query = "
        SELECT id, filename, token, created_at 
        FROM generated_cfdis 
        WHERE id IN ($placeholders)
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$ids);
}

$stmt->execute();
$result = $stmt->get_result();
$cfdis = $result->fetch_all(MYSQLI_ASSOC);

if (!$cfdis) {
    die("❌ CFDI no encontrado");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>CFDI generado</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="main">
    <div class="container form-centered">

        <div class="card">
            <h2>✅ CFDI generado con éxito</h2>

            <p>Tu CFDI fue generado correctamente.</p>

            <?php if (!$isMultiple): ?>
                <div class="plan-summary">
                    <b>ID:</b> <?= $cfdis[0]['id'] ?><br>
                    <b>Archivo:</b> <?= htmlspecialchars($cfdis[0]['filename']) ?><br>
                    <b>Fecha:</b> <?= $cfdis[0]['created_at'] ?>
                </div>
            <?php endif; ?>
            <?php if ($isMultiple): ?>

            <p><b><?= count($cfdis) ?> CFDI generados correctamente</b></p>

            <ul>
            <?php foreach ($cfdis as $c): ?>
                <li><?= htmlspecialchars($c['filename']) ?></li>
            <?php endforeach; ?>
            </ul>

            <?php endif; ?>

            <br>

            <!-- ✅ BOTÓN DESCARGA -->
            <?php if ($isMultiple): ?>

                <a href="<?=BASE_URL?>/backend/public/download_cfdis_zip.php?ids=<?= $idsParam ?>" class="btn blue">
                    ⬇ Descargar ZIP
                </a>

            <?php else: ?>

                <?php if ($isGuest): ?>
                    <button class="btn blue"
                        onclick="handleGuestDownload(<?= $cfdis[0]['id'] ?>)">
                        ⬇ Descargar CFDI
                    </button>
                <?php else: ?>
                    <a href="<?=BASE_URL?>/backend/public/download_cfdi_by_id.php?id=<?= $cfdis[0]['id'] ?>"
                    class="btn blue">
                    ⬇ Descargar CFDI
                    </a>
                <?php endif; ?>

            <?php endif; ?>

            <br>

            <!-- ✅ PDF -->
            <?php if (!$isMultiple): ?>
                <a href="<?=BASE_URL?>/backend/public/generate_cfdi_pdf.php?id=<?= $cfdis[0]['id'] ?>"
                class="btn purple"
                onclick="return confirmPDF();">
                🧾 Descargar PDF
                </a>
            <?php endif; ?>

            <!-- ✅ SOLO GUEST: correo -->
            <?php if ($isGuest): ?>
                <div style="margin-top:20px;">
                    <label><b>📧 Recibir por correo:</b></label><br>

                    <input type="email" id="guestEmail" placeholder="tu@email.com" class="input">

                    <button class="btn gray" style="margin-top:10px;" onclick="<?php if (!$isMultiple): ?> sendGuestEmail(<?= $cfdis[0]['id'] ?>) <?php endif; ?>">
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

    // ✅ descarga directa (sin backend intermedio)
    window.location.href = "<?= BASE_URL ?>/backend/public/download_cfdi_by_id.php?id=" + cfdiId;
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