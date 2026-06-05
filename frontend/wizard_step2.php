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
                <input type="text" id="root_name" name="root_name" required placeholder="Ej: Factura, AddendaDCM, Invoice">
                    <div class="hint">
                        <small>
                            Normalmente lo especifica tu cliente.
                        </small>
                    </div>
                <br>

                <label for="prefix">Prefijo del formato (opcional)</label>
                <input type="text" id="prefix" name="prefix" placeholder="Ej: THY, mabee">
                    <div class="hint">
                        <small>   
                            Si tu cliente no indicó ninguno, puedes dejarlo vacío.
                        </small>
                    </div>
                <br>
                <label for="namespace">Namespace del formato</label>
                <input
                    type="text"
                    id="namespace"
                    name="namespace"
                    placeholder="Ej: http://www.mycorp.com/schema"
                    required
                >
                <div class="hint">
                    <small>   
                        Este valor normalmente lo proporciona el cliente o viene en el <b>XML/XSD</b>.<br>
                    </small>
                </div>
                <br>
                <label>Namespace para etiqueta Addenda (opcional)</label>
                <input 
                    type="text" 
                    name="addenda_extra_ns" 
                    placeholder="Ej: xmlns:abc='http://cliente.com/addenda'"
                >
                <div class="hint">
                    <small>
                        Se agregará directamente en la etiqueta &lt;cfdi:Addenda&gt;
                    </small>
                </div>

                <button type="submit">Continuar al Paso 3</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>