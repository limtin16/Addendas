<?php
$path = "";
$depth = substr_count(__DIR__, DIRECTORY_SEPARATOR) - substr_count(__DIR__, DIRECTORY_SEPARATOR) + substr_count(substr(__DIR__, strpos(__DIR__, 'addendas')), DIRECTORY_SEPARATOR);
for ($i = 0; $i < $depth; $i++) {
    $path .= "../";
}
$path .= "backend/config.php";
require_once $path;

session_start();
require_once __DIR__ . '/../backend/db.php';
require_once dirname(__DIR__) . '/backend/helpers/auth.php';

// ✅ PROTEGER: solo usuarios logueados
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}

$userId = requireAuthAndPrivacy($conn);

// ✅ obtener templates del usuario
$stmt = $conn->prepare("
    SELECT id, name, created_at 
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

                <a href="<?= BASE_URL ?>/backend/public/load_template.php?id=<?= $tpl['id'] ?>" class="btn blue">
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