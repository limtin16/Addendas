<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
$cfgPath = $path . "backend/src/storage/cfdi_generated/";
$dbPath = $path . "backend/db.php";
require_once $dbPath;

$userId = $_SESSION['user_id'] ?? 4;

// ✅ XML obligatorio
$xml = $_POST['xml'] ?? null;
$originalName = $_POST['original_name'] ?? null;
$originalName = $originalName ?: '';

if (!$xml) {
    echo json_encode(['error' => 'No XML']);
    exit;
}

// ✅ generar filename
$filename = 'cfdi_' . time() . '.xml';

// ✅ guardar archivo físico
$path = __DIR__ . '/../src/storage/cfdi_generated/' . $filename;
// ✅ asegurar existencia de carpeta
if (!is_dir($cfgPath)) {
    mkdir($cfgPath, 0775, true);
}

$result = file_put_contents($path, $xml);
if (!is_writable($cfgPath)) {
    echo json_encode([
        'error' => 'Carpeta no escribible',
        'path' => $cfgPath
    ]);
    exit;
}
if ($result === false) {
    echo json_encode([
        'error' => 'No se pudo escribir archivo',
        'path' => $path,
        'is_writable' => is_writable($cfgPath),
        'dir_exists' => is_dir($cfgPath)
    ]);
    exit;
}

// ✅ token (para recuperación futura)
$token = bin2hex(random_bytes(8));

// ✅ guardar en BD (👀 user_id puede ser NULL)
$stmt = $conn->prepare("
    INSERT INTO generated_cfdis (user_id, filename, xml, token, original_name, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param("issss", $userId, $filename, $xml, $token, $originalName);
if (!$stmt) {
    echo json_encode([
        'error' => 'Prepare failed',
        'detail' => $conn->error
    ]);
    exit;
}
if (!$stmt->execute()) {
    echo json_encode([
        'error' => 'Execute failed',
        'detail' => $stmt->error
    ]);
    exit;
}
$stmt->execute();

$id = $stmt->insert_id;

// ✅ respuesta
// while (ob_get_level()) ob_end_clean();
echo json_encode([
    "id" => $id
]);