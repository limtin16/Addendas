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

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token inválido");
}
?>

<form action="<?= BASE_URL ?>/backend/public/update_password.php" method="POST">

    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <input type="password" name="password" placeholder="Nueva contraseña" required>

    <button type="submit">Actualizar contraseña</button>

</form>