<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$dbPath= $path . "backend/db.php";
$path.="backend/config.php";
require_once $path;
require_once $dbPath;

session_start();


$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    die("No autorizado");
}

$name = $_POST['name'] ?? null;
$cfdiId = $_POST['cfdi_id'] ?? null;
$templateId = $_POST['template_id'] ?? null;

if (!$name || !$templateId) {
    die("Datos incompletos");
}

// ✅ guardar template
$stmt = $conn->prepare("
    INSERT INTO templates (user_id, name, template_id) 
    VALUES (?, ?, ?)
");

$stmt->bind_param("iss", $userId, $name, $templateId);
$stmt->execute();

// ✅ redirigir bonito
header("Location: " . BASE_URL . "/frontend/templates_list.php");
exit;