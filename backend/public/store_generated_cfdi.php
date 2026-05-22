<?php
session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_POST['xml'])) {
    die("❌ No llegó XML");
}

$xml = $_POST['xml'];
$token = bin2hex(random_bytes(16));
$filename = "cfdi_" . $token . ".xml";
$path = dirname(__DIR__) . "/src/storage/cfdi_generated/" . $filename;
$result = file_put_contents($path, $xml);

if ($result === false) {
    die("❌ ERROR: no se pudo guardar el archivo en: " . $path);
}

echo "✅ Guardado correctamente en: " . $path;

if ($result === false) {
    die("❌ Error guardando archivo en: " . $path);
}

$userId = $_SESSION['user_id'] ?? null;
$stmt = $conn->prepare("
    INSERT INTO generated_cfdis (user_id, token, filename)
    VALUES (?, ?, ?)
");

$stmt->bind_param("iss", $userId, $token, $filename);
$stmt->execute();

$_SESSION['generated_cfdi_file'] = $filename;
$_SESSION['generated_cfdi_token'] = $token;

header('Content-Type: application/json');

echo json_encode([
    'ok' => true,
    'token' => $token
]);

exit;