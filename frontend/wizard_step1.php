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

// wizard_step1.php
session_start();

$_SESSION['addenda_mode'] = 'manual';

if (
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['guest_paid'])
) {
    header("Location: " . BASE_URL . "/frontend/select_mode.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear plantilla – Paso 1</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container">

        <div class="card tooltip-safe">
            <h2>Crear plantilla – Paso 1</h2>
            <p>Define la información básica de la plantilla.</p>

            <form method="POST" action="<?= BASE_URL ?>/backend/public/save_template_step1.php">
                <label for="name">
                    Nombre de la plantilla
                    <span class="tooltip" data-tooltip="Asigna un nombre para identificacion, no affecta la addenda en si">
                        ℹ️
                    </span>
                </label>
                <input type="text" id="name" name="name" required>
                <div class="hint">Ejemplo: Addenda Mycorp </div>
                <button type="submit">Continuar al Paso 2</button>
            </form>
        </div>
    </div>
</div>

</body>
</body>
</html>