<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ includes base
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
    $path.="../";
}

require_once $path . "backend/config.php";
require_once $path . "backend/db.php";
require_once $path . "backend/helpers/mailer.php";

// ✅ leer input
$data = json_decode(file_get_contents("php://input"), true);

$cfdiId = $data['cfdi_id'] ?? null;
$email = $data['email'] ?? null;

if (!$cfdiId || !$email) {
    echo json_encode(["success" => false, "error" => "Datos inválidos"]);
    exit;
}

// ✅ validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Email inválido"]);
    exit;
}

// ✅ obtener CFDI
$stmt = $conn->prepare("
    SELECT id, filename, token, created_at 
    FROM generated_cfdis 
    WHERE id = ?
");

$stmt->bind_param("i", $cfdiId);
$stmt->execute();
$result = $stmt->get_result();
$cfdi = $result->fetch_assoc();

if (!$cfdi) {
    echo json_encode(["success" => false, "error" => "CFDI no encontrado"]);
    exit;
}

// ✅ generar link temporal (token ya existente)
$downloadLink = BASE_URL_FULL . "/backend/public/download_cfdi_by_id.php?token=" . urlencode($cfdi['token']);

// ✅ obtener template
$stmt = $conn->prepare("
    SELECT subject, body 
    FROM email_templates 
    WHERE code = 'guest_cfdi_download'
    LIMIT 1
");

$stmt->execute();
$stmt->bind_result($subject, $templateBody);
$stmt->fetch();
$stmt->close();

// ✅ variables
$vars = [
    'filename' => $cfdi['filename'],
    'date' => date('d/m/Y H:i'),
    'download_link' => $downloadLink
];

// ✅ render
$body = renderTemplate($templateBody, $vars);

// ✅ enviar email
$status = sendEmail($email, $subject, $body) ? 'sent' : 'error';

// ✅ log BD
$logStmt = $conn->prepare("
    INSERT INTO email_logs (user_id, email, template_code, status)
    VALUES (?, ?, 'guest_cfdi_download', ?)
");

$userId = 4; // guest interno

$logStmt->bind_param("iss", $userId, $email, $status);
$logStmt->execute();
$logStmt->close();

// ✅ respuesta
echo json_encode(["success" => true]);