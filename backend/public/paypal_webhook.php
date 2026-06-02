<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$mailerPath = $path . "backend/helpers/mailer.php";
$dbPath = $path . "backend/db.php";
$path.="backend/config.php";
require_once $path;
require_once $dbPath;
require_once $mailerPath;

// ✅ leer JSON enviado desde frontend
$payload = file_get_contents("php://input");
$data = json_decode($payload);

// ✅ log para debug
file_put_contents(__DIR__ . "/paypal_log.txt", $payload . "\n\n", FILE_APPEND);

// ✅ validar datos
if (!$data || !isset($data->id)) {
    http_response_code(400);
    echo "Pago inválido";
    exit;
}

// ✅ obtener info de la orden
$orderId = $data->id;
$purchaseUnit = $data->purchase_units[0];

$amount = $purchaseUnit->amount->value;

// ✅ metadata personalizada (credits)
$custom = json_decode($purchaseUnit->custom_id ?? '{}');
$credits = $custom->credits ?? 0;

// ✅ obtener usuario desde sesión
session_start();
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$credits) {
    http_response_code(400);
    echo "Datos inválidos";
    exit;
}

// ✅ evitar duplicados
$stmt = $conn->prepare("
    SELECT id FROM payments 
    WHERE external_order_id = ? AND provider = 'paypal'
");
$stmt->bind_param("s", $orderId);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();

if ($exists) {
    echo "Pago ya procesado";
    exit;
}

// ✅ crear créditos
$stmt = $conn->prepare("
    INSERT INTO user_credit_batches 
    (user_id, credits, remaining_credits, expires_at, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

$expiresAt = date('Y-m-d H:i:s', strtotime('+1 year'));

$stmt->bind_param("iiis", $userId, $credits, $credits, $expiresAt);

if (!$stmt->execute()) {
    die("Error insert credit batch: " . $stmt->error);
}

// ✅ guardar pago
$provider = 'paypal';

$stmt = $conn->prepare("
    INSERT INTO payments 
    (user_id, credits, provider, external_order_id, amount)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("iissd", $userId, $credits, $provider, $orderId, $amount);
$stmt->execute();

// ✅ obtener template compra
$stmt = $conn->prepare("
    SELECT subject, body 
    FROM email_templates 
    WHERE code = 'purchase_confirmation' 
    LIMIT 1
");
$stmt->execute();
$stmt->bind_result($subject, $templateBody);
$stmt->fetch();
$stmt->close();

// ✅ variables template compra
$vars = [
    'order_id' => $orderId,
    'date' => date('d/m/Y H:i'),
    'credits' => $credits,
    'amount' => number_format($amount, 2),
    'dashboard_url' => BASE_URL_FULL . "/frontend/dashboard.php"
];

$body = renderTemplate($templateBody, $vars);

// ✅ obtener email usuario
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($userEmail);
$stmt->fetch();
$stmt->close();

// ✅ enviar correo compra
sendEmail($userEmail, $subject, $body);

