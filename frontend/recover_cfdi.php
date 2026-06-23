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
<html>
<head>
    <title>Recuperar CFDI</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">
    <style>
/* ✅ CONTENEDOR ESPECÍFICO */
.recover-wrapper {
    display: flex;
    justify-content: center;
    padding-top: 80px;
}

/* ✅ CAJA */
.recover-box {
    background: white;
    padding: 30px;
    border-radius: 10px;
    width: 350px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* ✅ INPUT SOLO AQUÍ */
.recover-box input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* ✅ BOTÓN SOLO AQUÍ */
.recover-box button {
    padding: 10px;
    width: 100%;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

/* ✅ HOVER */
.recover-box button:hover {
    background: #0056b3;
}
</style>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container form-centered">

        <div class="card" style="max-width: 400px; width: 100%;">

            <h2>🧾 Recuperar CFDI</h2>

            <p style="font-size:13px; color:#666;">
                Ingresa el token que recibiste por correo para descargar tu CFDI.
            </p>

            <form method="GET" action="<?= BASE_URL ?>/backend/public/recover_cfdi.php">

                <input 
                    type="text" 
                    name="token" 
                    placeholder="Ingresa tu token" 
                    required
                    class="input"
                >

                <button class="btn blue full" style="margin-top:10px;">
                    ⬇ Descargar CFDI
                </button>

            </form>

        </div>

    </div>
</div>

</body>
</html>