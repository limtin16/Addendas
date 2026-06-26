<?php

$path="";
$count = (substr_count(substr(getcwd(), strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count = (substr_count(substr(getcwd(), strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
    $path.="../";
}

$dbPath = $path . "backend/db.php";
$authPath = $path . "backend/helpers/auth.php";
$path.="backend/config.php";

require_once $path;
require_once $dbPath;
require_once $authPath;

session_start();

// ✅ PROTECCIÓN
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}

// ✅ obtener templates globales (usuario sistema)
$stmt = $conn->prepare("
    SELECT id, name, created_at, template_id 
    FROM templates 
    WHERE user_id = ?
    ORDER BY name ASC
");
$systemUserId = SYSTEM_USER_ID;
$stmt->bind_param("i", $systemUserId);
$stmt->execute();

$result = $stmt->get_result();
$templates = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Addendas predefinidas</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container">
        <div class="market-header">
            <h2>Addendas predefinidas</h2>
            <p>Selecciona una plantilla lista para usar y genera tu CFDI en segundos.</p>
        </div>
        <?php if (empty($templates)): ?>
            <p class="empty">No hay addendas predefinidas disponibles aún.</p>
        <?php else: ?>
            <div class="market-grid">
                <?php foreach ($templates as $tpl): 
                    ?>
                    <div class="market-card">
                        <!-- LOGO -->
                        <div class="market-logo">
                            <a href="<?= BASE_URL ?>/frontend/render_instance_form.php?template_id=<?= urlencode($tpl['template_id']) ?>">
                                <img
                                    src="<?= BASE_URL ?>/frontend/assets/logos/<?= strtolower(str_replace(' ', '_', $tpl['name'])) ?>.png"
                                    onerror="this.src='<?= BASE_URL ?>/frontend/assets/logos/default.png'"
                                    alt="<?= htmlspecialchars($tpl['name']) ?>">
                            </a>
                        </div>
                        <!-- INFO -->
                        <div class="market-info">

                            <div class="market-title">
                                <?= htmlspecialchars($tpl['name']) ?>
                            </div>

                            <div class="market-sub">
                                Addenda preconfigurada
                            </div>

                        </div>

                        <!-- ACTION -->
                        <a href="<?= BASE_URL ?>/frontend/render_instance_form.php?template_id=<?= urlencode($tpl['template_id']) ?>" class="btn blue">
                            Usar addenda
                        </a>

                    </div>

                <?php endforeach; ?>

                </div>
        <?php endif; ?>
        <a class="back" href="<?= BASE_URL ?>/frontend/dashboard.php">
                ⬅ Volver
        </a>
    </div>
</div>
</body>
</html>