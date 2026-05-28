<?php

$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$path.="backend/config.php";
require_once $path;
require_once __DIR__ . '/../db.php';

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

    // ✅ agregar créditos
    $stmt = $conn->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
    $stmt->bind_param("ii", $credits, $userId);
    $stmt->execute();

    // ✅ guardar historial
    $stmt = $conn->prepare("
        INSERT INTO payments (user_id, credits, conekta_order_id, amount)
        VALUES (?, ?, ?, ?)
    ");

    $amount = $order->amount / 100; // centavos → pesos

    $stmt->bind_param("iisd", $userId, $credits, $orderId, $amount);
    $stmt->execute();

    echo "OK";
    exit;
}

// ✅ ignorar otros eventos
echo "Evento ignorado";