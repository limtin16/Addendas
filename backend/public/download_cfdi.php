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
require_once dirname(__DIR__) . '/db.php';

// ✅ validar login
if (!isset($_SESSION['user_id'])) {
    die("No autorizado");
}

$id = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// ✅ obtener XML desde BD
$stmt = $conn->prepare("
    SELECT filename, xml 
    FROM generated_cfdis
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();

$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    die("CFDI no encontrado");
}

// ✅ descargar DIRECTO desde BD
header('Content-Type: application/xml');
header('Content-Disposition: attachment; filename="' . $res['filename'] . '"');

echo $res['xml'];
exit;