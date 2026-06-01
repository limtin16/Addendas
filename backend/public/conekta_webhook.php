<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// ✅ leer JSON de Conekta
$payload = file_get_contents("php://input");
$data = json_decode($payload);

// ✅ log para debug (importante al inicio)
file_put_contents(__DIR__ . "/webhook_log.txt", $payload . "\n\n", FILE_APPEND);

// ✅ validar evento
if (!isset($data->type)) {
    http_response_code(400);
    echo "Evento inválido";
    exit;
}

// ✅ SOLO PROCESAR PAGOS EXITOSOS
if ($data->type === "order.paid") {

    $order = $data->data->object;

    // ✅ metadata que tú enviaste
    $userId = $order->metadata->user_id ?? null;
    $credits = $order->metadata->credits ?? 0;

    if (!$userId || !$credits) {
        http_response_code(400);
        echo "Metadata inválida";
        exit;
    }

    // ✅ evitar duplicados (muy importante)
    $orderId = $order->id;

    $stmt = $conn->prepare("SELECT id FROM payments WHERE conekta_order_id = ?");
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();

    if ($exists) {
        echo "Orden ya procesada";
        exit;
    }

    // ✅ crear lote de créditos
    $stmt = $conn->prepare("
        INSERT INTO user_credit_batches 
        (user_id, credits, remaining_credits, expires_at, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");

    // ejemplo: expiran en 1 año
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 year'));

    $remainingCredits = $credits;

    $stmt->bind_param("iiis", 
        $userId, 
        $credits, 
        $remainingCredits, 
        $expiresAt
    );

    if (!$stmt->execute()) {
        throw new Exception("Error insert credit batch: " . $stmt->error);
    }

    // ✅ guardar historial
    $stmt = $conn->prepare("
        INSERT INTO payments (user_id, credits, conekta_order_id, amount)
        VALUES (?, ?, ?, ?)
    ");

    $amount = $order->amount / 100; // centavos → pesos

    $stmt->bind_param("iisd", $userId, $credits, $orderId, $amount);
    $stmt->execute();

    $stmt = $conn->prepare("SELECT subject, body FROM email_templates WHERE code = 'purchase_confirmation' LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($subject, $templateBody);
    $stmt->fetch();
    $stmt->close();

    $vars = [
        'order_id' => $orderId,
        'date' => date('Y-m-d H:i'),
        'credits' => $credits,
        'amount' => number_format($amount, 2),
        'dashboard_url' => BASE_URL_FULL . "/frontend/dashboard.php"
    ];

    $body = renderTemplate($templateBody, $vars);

    // ✅ obtener email del usuario
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userEmail);
    $stmt->fetch();
    $stmt->close();
    
    sendEmail(
        $userEmail,
        $subject,
        $body
    );

    echo "OK";
    exit;
}

// ✅ ignorar otros eventos
echo "Evento ignorado";