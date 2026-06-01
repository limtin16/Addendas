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
$mailerPath = $path . "backend/helpers/mailer.php";
$path.="backend/config.php";
require_once $path;
require_once $dbPath;
require_once $mailerPath;
session_start();

$userId = $_SESSION['user_id'] ?? null;
$purchaseId = $_POST['purchase_id'] ?? null;

if (!$userId || !$purchaseId) {
    die("Datos inválidos");
}

// ✅ guardar solicitud
$stmt = $conn->prepare("
    INSERT INTO invoice_requests (user_id, purchase_id)
    VALUES (?, ?)
");
$stmt->bind_param("ii", $userId, $purchaseId);
$stmt->execute();
$stmt->close();

// ✅ obtener datos fiscales
$stmt = $conn->prepare("
    SELECT rfc, name, postal_code, regime, cfdi_use, email
    FROM billing_profiles
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($rfc, $name, $postalCode, $regime, $cfdiUse, $email);
$stmt->fetch();
$stmt->close();

// ✅ obtener template
$stmt = $conn->prepare("
    SELECT subject, body 
    FROM email_templates 
    WHERE code = 'invoice_request' 
    LIMIT 1
");
$stmt->execute();
$stmt->bind_result($subject, $templateBody);
$stmt->fetch();
$stmt->close();

// ✅ variables para template
$vars = [
    'user_id' => $userId,
    'purchase_id' => $purchaseId,
    'date' => date('d/m/Y H:i'),
    'rfc' => $rfc,
    'name' => $name,
    'postal_code' => $postalCode,
    'regime' => $regime,
    'cfdi_use' => $cfdiUse,
    'email' => $email
];

// ✅ renderizar template
$body = renderTemplate($templateBody, $vars);

// ✅ enviar email al soporte
sendEmail(
    "support@addendafacil.com",
    $subject,
    $body
);

// ✅ respuesta
header("Location: " . BASE_URL . "/frontend/billing.php?success=1");
exit;