<?php

// ✅ muestra errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ leer payload JSON
$input = file_get_contents("php://input");
$data = json_decode($input);

// ✅ opcional: guardar log para debugging
file_put_contents(
    __DIR__ . '/webhook_log.txt',
    date('Y-m-d H:i:s') . " - " . $input . PHP_EOL,
    FILE_APPEND
);

// ✅ validar que exista evento
if (!isset($data->type)) {
    http_response_code(400);
    exit("Evento inválido");
}

// ✅ procesar eventos
switch ($data->type) {

    case "order.paid":

        $order = $data->data->object;

        $orderId = $order->id;
        $amount = $order->amount;
        $metadata = $order->metadata ?? null;

        // ✅ EJEMPLO: sacar user_id
        $userId = $metadata->user_id ?? null;
        $credits = $metadata->credits ?? 1;

        // ✅ AQUÍ asignas créditos en DB
        // (esto es ejemplo)
        if ($userId) {

            require_once __DIR__ . '/../config.php';
            require_once __DIR__ . '/../db.php';

            $stmt = $conn->prepare("
                UPDATE users 
                SET credits = credits + ? 
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $credits, $userId);
            $stmt->execute();
        }

        break;

    default:
        // otros eventos si quieres
        break;
}

// ✅ responder OK a Conekta
http_response_code(200);
echo "OK";