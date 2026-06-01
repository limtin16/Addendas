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
$path.="backend/config.php";
require_once $path;
require_once $dbPath;
session_start();

$userId = $_SESSION['user_id'] ?? null;
$purchaseId = $_POST['purchase_id'] ?? null;

if (!$purchaseId) {
    die("Compra inválida");
}

// ✅ guardar solicitud
$stmt = $conn->prepare("
INSERT INTO invoice_requests (user_id, purchase_id)
VALUES (?, ?)
");
$stmt->bind_param("ii", $userId, $purchaseId);
$stmt->execute();

// ✅ datos del cliente
$stmt = $conn->prepare("SELECT * FROM billing_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// ✅ correo
$to = "rene.limonchi@addendafacil.com";
$subject = "🧾 Nueva solicitud de factura";

$message = "
Solicitud de factura:

Compra ID: $purchaseId

RFC: {$data['rfc']}
Nombre: {$data['name']}
CP: {$data['postal_code']}
Régimen: {$data['regime']}
Uso CFDI: {$data['cfdi_use']}
Email: {$data['email']}

Fecha: ".date('Y-m-d H:i:s');

mail($to, $subject, $message);

// ✅ respuesta elegante
header("Location: " . BASE_URL . "/frontend/billing.php?success=1");
exit;