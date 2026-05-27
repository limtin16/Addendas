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
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container">

        <div class="card">
            <h2>Crear plantilla – Paso 1</h2>
            <p>Define la información básica de la plantilla.</p>

            <form method="POST" action="<?= BASE_URL ?>/backend/public/save_template_step1.php">
                
                <label for="name">Nombre de la plantilla</label>
                <input type="text" id="name" name="name" required>
                <div class="hint">Ejemplo: Addenda Thyssenkrupp Factura</div>

                <label for="location">¿Dónde se usará esta información?</label>
                <select id="location" name="location" required>
                    <option value="">Selecciona una opción</option>
                    <option value="ADDENDA">Addenda (información adicional del cliente)</option>
                    <option value="COMPLEMENTO">Complemento (información técnica especial)</option>
                </select>

                <button type="submit">Continuar al Paso 2</button>
            </form>
        </div>
    </div>
</div>

</body>
</body>
</html>