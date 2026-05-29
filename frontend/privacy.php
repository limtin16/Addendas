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
require_once dirname(__DIR__) . '../backend/db.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT content, version 
    FROM privacy_policy 
    WHERE active = 1 
    ORDER BY id DESC 
    LIMIT 1
");

$stmt->execute();
$policy = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aviso de Privacidad</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/styles.css">
    <style>

/* ✅ RESET TOTAL para esta página */
body {
    margin: 0;
    padding: 0;
}

/* ✅ eliminar restricciones globales */
.container, .main {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* ✅ layout FULLSCREEN REAL */
.privacy-wrapper {
    display: flex;
    flex-direction: column;
    height: 100vh;
    width: 100%;
    background: #f9fafb;
}

/* ✅ contenido centrado ancho real */
.privacy-content {
    flex: 1;
    overflow-y: auto;
    max-width: 900px;
    margin: auto;
    padding: 40px 20px;
    background: #fff;
}

/* ✅ footer SIEMPRE visible */
.privacy-footer {
    position: sticky;
    bottom: 0;
    width: 100%;
    background: #fff;
    border-top: 1px solid #eee;
    padding: 15px 20px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
}

</style>
</head>
<body>

<div class="container">
<div class="privacy-page">

    <div class="privacy-container">

        <header class="privacy-header">
            <h1>📄 Aviso de Privacidad</h1>
            <p>Revisa los términos antes de continuar</p>
        </header>

        <form method="POST" action="<?= BASE_URL ?>/backend/public/accept_privacy.php">

            <!-- ✅ CONTENIDO CON SCROLL -->
            <div class="privacy-content">

                <?= $policy['content'] ?>

                <p class="date">
                    Versión: <?= $policy['version'] ?> |
                    Última actualización: <?= date('Y-m-d') ?>
                </p>

            </div>

            <!-- ✅ FOOTER FIJO -->
            <div class="privacy-footer">

                <label class="privacy-check">
                    <input type="checkbox" name="accept_privacy" required>
                    He leído y acepto el aviso de privacidad
                </label>

                <button type="submit" class="btn blue">
                    ✅ Aceptar y continuar
                </button>

            </div>

        </form>

    </div>

</div>
</div>

</body>
</html>