<?php
session_start();
header('Content-Type: application/json');
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$cfgPath = rtrim($path, '/\\') . '/backend/src/storage/cfdi_generated/';
$dbPath = $path . "backend/db.php";
require_once $dbPath;

$userId = $_SESSION['user_id'] ?? null;

// ✅ XML obligatorio
$xml = $_POST['xml'] ?? null;

if (!$xml) {
    echo json_encode(['error' => 'No XML']);
    exit;
}

// ✅ generar filename
$filename = 'cfdi_' . time() . '.xml';

// ✅ guardar archivo físico
$path = $cfgPath . $filename;
file_put_contents($path, $xml);

// ✅ token (para recuperación futura)
$token = bin2hex(random_bytes(8));

// ✅ guardar en BD (👀 user_id puede ser NULL)
$stmt = $conn->prepare("
    INSERT INTO generated_cfdis (user_id, filename, xml, token, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

$stmt->bind_param("isss", $userId, $filename, $xml, $token);
$stmt->execute();

$id = $stmt->insert_id;

// ✅ respuesta
echo json_encode([
    "id" => $id
]);