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

// ✅ detectar usuario
$isLogged = !empty($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;

$id = $_GET['id'] ?? 0;

if (!$id) {
    die("ID inválido");
}

// ✅ query dinámica
if ($isLogged) {

    $stmt = $conn->prepare("
        SELECT filename 
        FROM generated_cfdis 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $id, $userId);

} else {

    // ✅ visitante → solo por ID
    $stmt = $conn->prepare("
        SELECT filename 
        FROM generated_cfdis 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $id);

}

$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    die("CFDI no encontrado");
}

// ✅ ruta
$path = dirname(__DIR__) . "/src/storage/cfdi_generated/" . $res['filename'];

if (!file_exists($path)) {
    die("Archivo no existe");
}

// ✅ descarga
header('Content-Type: application/xml');
header('Content-Disposition: attachment; filename="' . $res['filename'] . '"');

readfile($path);
exit;