<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$dbPath = $path . "backend/db.php";
require_once $dbPath;


$regime = $_GET['regime'] ?? null;

$stmt = $conn->prepare("
    SELECT u.code, u.description
    FROM sat_uso_cfdi u
    JOIN sat_regimen_uso r ON u.code = r.uso_cfdi_code
    WHERE r.regimen_code = ?
");

$stmt->bind_param("s", $regime);
$stmt->execute();

echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));