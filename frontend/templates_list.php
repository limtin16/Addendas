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

$userId = requireAuthAndPrivacy($conn);
$creditService = new CreditService($conn);
$credits = $creditService->getAvailableCredits($userId);

// ✅ PROTEGER: solo usuarios logueados
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}

// ✅ obtener templates del usuario
$stmt = $conn->prepare("
    SELECT id, name, created_at, template_id 
    FROM templates 
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$templates = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mis Templates</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">
</head>

<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="main">
    <div class="container">

        <h2>Mis Templates</h2>

        <?php if (empty($templates)): ?>
            <p class="empty">No tienes templates guardados aún.</p>
        <?php else: ?>

            <?php foreach ($templates as $tpl): ?>
                <div class="template">
                <div class="name"><?= htmlspecialchars($tpl['name']) ?></div>
                <div class="date">Creado: <?= $tpl['created_at'] ?></div>

                <div class="actions">

                <a href="<?= BASE_URL ?>/frontend/render_instance_form.php?template_id=<?= $tpl['template_id'] ?>" class="btn blue">
                    Usar template
                </a>

                <form method="POST" action="<?= BASE_URL ?>/backend/public/delete_template.php">
                    <input type="hidden" name="id" value="<?= $tpl['id'] ?>">
                    <button class="btn delete">Eliminar</button>
                </form>

            </div>

            </div>
            <?php endforeach; ?>

        <?php endif; ?>

            <a class="back" href="<?= BASE_URL ?>/frontend/dashboard.php">
                ⬅ Volver
            </a>

    </div>
</div>

</body>
</html>