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
require_once $path . "backend/helpers/paypal.php";
require_once $path . "backend/db.php";

// ✅ leer webhook
$payload = file_get_contents("php://input");
$data = json_decode($payload);

// ✅ log
file_put_contents(__DIR__ . "/guest_paypal_log.txt",
    date('Y-m-d H:i:s') . "\n" . $payload . "\n\n",
    FILE_APPEND
);

// ✅ validar evento
if (!$data || !isset($data->event_type)) {
    http_response_code(400);
    exit("Evento inválido");
}

// ✅ solo pagos completados
if ($data->event_type !== "PAYMENT.CAPTURE.COMPLETED") {
    exit("Evento ignorado");
}

// ✅ capture
$capture = $data->resource ?? null;
if (!$capture) {
    http_response_code(400);
    exit("Sin resource");
}

// ✅ order_id
$orderId = $capture->supplementary_data->related_ids->order_id ?? null;
if (!$orderId) {
    http_response_code(400);
    exit("Sin orderId");
}

// ✅ validar contra PayPal (igual que tu sistema actual)
$paypalOrder = verifyPayPalOrder($orderId);
if (!$paypalOrder || $paypalOrder->status !== 'COMPLETED') {
    http_response_code(400);
    exit("Pago no validado");
}

// ✅ obtener datos
$purchaseUnit = $paypalOrder->purchase_units[0];
$amount = $purchaseUnit->amount->value ?? 0;

// ✅ metadata
$custom = json_decode($purchaseUnit->custom_id ?? '{}');

$type = $custom->type ?? null;
$redirect = $custom->redirect ?? null;

if ($type !== "guest_addenda") {
    exit("No es guest checkout");
}

// ✅ EVITAR DUPLICADOS
$file = __DIR__ . "/guest_payments.log";

if (file_exists($file)) {
    $logs = file($file);
    foreach ($logs as $line) {
        if (strpos($line, $orderId) !== false) {
            exit("Pago ya procesado");
        }
    }
}

// ✅ EVITAR DUPLICADOS en DB (igual que tu webhook original)
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

// ✅ INSERTAR pago (guest = user_id 4, 1 crédito)
$userId = 4;
$credits = 1;

$stmt = $conn->prepare("
    INSERT INTO payments   
    (user_id, credits, provider, external_order_id, amount)  
    VALUES (?, ?, 'paypal', ?, ?)
");

$stmt->bind_param("iisd", $userId, $credits, $orderId, $amount);

if (!$stmt->execute()) {
    file_put_contents(__DIR__ . "/guest_paypal_error.log",
        "Error insert payment: " . $stmt->error . "\n",
        FILE_APPEND
    );
    exit;
}

// ✅ opcional: log simple adicional
file_put_contents($file,
    json_encode([
        "order_id" => $orderId,
        "amount" => $amount,
        "redirect" => $redirect,
        "date" => date('Y-m-d H:i:s')
    ]) . "\n",
    FILE_APPEND
);

echo "OK";