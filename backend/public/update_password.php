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
$path.="backend/config.php";
require_once $path;
require_once $dbPath;


$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';

$success = false;
$errorMsg = '';

if (!$token || !$password) {
    $errorMsg = "Datos inválidos";
} else {

    // ✅ buscar usuario
    $stmt = $conn->prepare("
        SELECT id, reset_expires
        FROM users
        WHERE reset_token = ?
    ");

    if (!$stmt) {
        $errorMsg = "Error SQL: " . $conn->error;
    } else {

        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->bind_result($userId, $expires);
        $stmt->fetch();
        $stmt->close();

        if (!$userId) {
            $errorMsg = "Token inválido";
        } elseif (strtotime($expires) < time()) {
            $errorMsg = "El enlace ha expirado";
        } else {

            // ✅ hash password
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("
                UPDATE users
                SET password = ?, reset_token = NULL, reset_expires = NULL
                WHERE id = ?
            ");

            if ($stmt) {
                $stmt->bind_param("si", $hash, $userId);
                $stmt->execute();
                $stmt->close();
                $success = true;
            } else {
                $errorMsg = "Error al actualizar contraseña";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar contraseña</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles.css">

    <?php if ($success): ?>
        <!-- ✅ redirect solo si éxito -->
        <meta http-equiv="refresh" content="4;url=<?= BASE_URL ?>/frontend/login.php">
    <?php endif; ?>

</head>
<body>

<div class="simple-page">

    <div class="simple-box">

        <?php if ($success): ?>

            <h2>✅ Contraseña actualizada</h2>

            <p class="description">
                Tu contraseña se actualizó correctamente.
            </p>

            <p style="font-size:13px; color:#666;">
                Serás redirigido al login en unos segundos...
            </p>

            <a href="<?= BASE_URL ?>/frontend/login.php" class="btn blue full">
                Ir al login
            </a>

        <?php else: ?>

            <h2>❌ Error</h2>

            <p class="description">
                <?= htmlspecialchars($errorMsg) ?>
            </p>

            <a href="<?= BASE_URL ?>/frontend/forgot_password.php" class="btn gray full">
                Intentar de nuevo
            </a>

        <?php endif; ?>

    </div>

</div>

</body>
</html>