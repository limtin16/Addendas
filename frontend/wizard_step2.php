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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear plantilla – Paso 2</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container">
        <div class="card">
            <h2>Crear plantilla – Paso 2</h2>
            <p>Define la estructura principal solicitada por tu cliente.</p>

            <form method="post" action="<?= BASE_URL ?>/backend/public/save_template_step2.php">
                <!-- Id de la plantilla creada en el paso 1 -->
                <input type="hidden" name="template_id" value="<?php echo $_GET['template_id'] ?? ''; ?>">

                <label for="root">Nombre de la sección principal</label>
                <input type="text" id="root_name" name="root_name" required>
                <div class="hint">
                    Normalmente lo especifica tu cliente.  
                    Ejemplos: <b>Factura</b>, <b>AddendaDCM</b>, <b>Invoice</b>
                </div>

                <label for="prefix">Prefijo del formato (opcional)</label>
                <input type="text" id="prefix" name="prefix">
                <div class="hint">
                    Ejemplos: <b>THY</b>, <b>mabee</b>.  
                    Si tu cliente no indicó ninguno, puedes dejarlo vacío.
                </div>
                <label for="namespace">Namespace del formato</label>
                <input
                    type="text"
                    id="namespace"
                    name="namespace"
                    required
                >
                <div class="hint">
                    Este valor normalmente lo proporciona el cliente o viene en el XML/XSD.<br>
                    Ejemplo: <code>http://www.mycorp.com/schema</code>
                </div>

                <button type="submit">Continuar al Paso 3</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>