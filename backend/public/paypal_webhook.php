<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ includes
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
    $path.="../";
}

$mailerPath = $path . "backend/helpers/mailer.php";
$paypalHelperPath = $path . "backend/helpers/paypal.php";
$dbPath = $path . "backend/db.php";
$path.="backend/config.php";

require_once $path;
require_once $dbPath;
require_once $mailerPath;
require_once $paypalHelperPath;


// ✅ leer webhook
$payload = file_get_contents("php://input");
$data = json_decode($payload);

// ✅ log SIEMPRE
file_put_contents(__DIR__ . "/paypal_log.txt", date('Y-m-d H:i:s') . "\n" . $payload . "\n\n", FILE_APPEND);


// ✅ validar estructura
if (!$data || !isset($data->event_type)) {
    http_response_code(400);
    echo "Evento inválido";
    exit;
}

// ✅ solo pagos completados
if ($data->event_type !== "PAYMENT.CAPTURE.COMPLETED") {
    echo "Evento ignorado";
    exit;
}

// ✅ obtener capture
$capture = $data->resource ?? null;

if (!$capture) {
    http_response_code(400);
    echo "Sin resource";
    exit;
}

// ✅ obtener order_id
$orderId = $capture->supplementary_data->related_ids->order_id ?? null;

if (!$orderId) {
    http_response_code(400);
    echo "Sin orderId";
    exit;
}


// ✅ VALIDAR CON PAYPAL
$paypalOrder = verifyPayPalOrder($orderId);

if (!$paypalOrder || $paypalOrder->status !== 'COMPLETED') {
    http_response_code(400);
    echo "Pago no validado";
    exit;
}


// ✅ obtener datos desde PayPal (fuente confiable)
$purchaseUnit = $paypalOrder->purchase_units[0];

$amount = $purchaseUnit->amount->value ?? 0;

// ✅ metadata segura
$custom = json_decode($purchaseUnit->custom_id ?? '{}');

$userId = $custom->user_id ?? null;
$credits = $custom->credits ?? 0;

if (!$userId || !$credits) {
    http_response_code(400);
    echo "Metadata inválida";
    exit;
}


// ✅ EVITAR DUPLICADOS (IMPORTANTE)
$stmt = $conn->prepare("
    SELECT id FROM payments 
    WHERE external_order_id = ? AND provider = 'paypal'
");

$stmt->bind_param("s", $orderId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Pago ya procesado";
    exit;
}


// ✅ CREAR CRÉDITOS
$stmt = $conn->prepare("
    INSERT INTO user_credit_batches 
    (user_id, credits, remaining_credits, expires_at, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

$expiresAt = date('Y-m-d H:i:s', strtotime('+1 year'));

$stmt->bind_param("iiis", $userId, $credits, $credits, $expiresAt);

if (!$stmt->execute()) {
    file_put_contents(__DIR__ . "/paypal_error.log", "Error batch: " . $stmt->error . "\n", FILE_APPEND);
    exit;
}


// ✅ GUARDAR PAGO
$stmt = $conn->prepare("
    INSERT INTO payments 
    (user_id, credits, provider, external_order_id, amount)
    VALUES (?, ?, 'paypal', ?, ?)
");

$stmt->bind_param("iisd", $userId, $credits, $orderId, $amount);

if (!$stmt->execute()) {
    file_put_contents(__DIR__ . "/paypal_error.log", "Error payment: " . $stmt->error . "\n", FILE_APPEND);
    exit;
}


// ✅ EMAIL DE CONFIRMACIÓN
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

$vars = [
    'order_id' => $orderId,
    'date' => date('d/m/Y H:i'),
    'credits' => $credits,
    'amount' => number_format($amount, 2),
    'dashboard_url' => BASE_URL_FULL . "/frontend/dashboard.php"
];

$body = renderTemplate($templateBody, $vars);

// ✅ obtener email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($userEmail);
$stmt->fetch();
$stmt->close();
// ✅ enviar correo
$status = sendEmail($userEmail, $subject, $body) ? 'sent' : 'error';

// ✅ registrar envío de correo
$logStmt = $conn->prepare("
    INSERT INTO email_logs (user_id, email, template_code, status)
    VALUES (?, ?, 'purchase_confirmation', ?)
");

$logStmt->bind_param("iss", $userId, $userEmail, $status);
$logStmt->execute();
$logStmt->close();

// ✅ FINAL
echo "OK";