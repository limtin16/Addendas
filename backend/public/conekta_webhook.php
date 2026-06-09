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

    $stmt = $conn->prepare("SELECT id FROM payments WHERE external_order_id = ? AND provider = 'conekta'");
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
   $provider = 'conekta';

$stmt = $conn->prepare("
    INSERT INTO payments 
    (user_id, credits, provider, external_order_id, amount)
    VALUES (?, ?, ?, ?, ?)
");

    $amount = $order->amount / 100; // centavos → pesos

    $stmt->bind_param("iissd", $userId, $credits, $provider, $orderId, $amount);
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

    // ✅ log de envío de correo
    $log = $conn->prepare("
        INSERT INTO email_logs (user_id, email, template_code, status)
        VALUES (?, ?, 'purchase_confirmation', 'sent')
    ");
    $log->bind_param("is", $userId, $userEmail);
    $log->execute();
    $log->close();

    // ✅ revisar si auto factura está activada
        $stmt = $conn->prepare("
            SELECT auto_invoice, rfc, name, postal_code, regime, cfdi_use, email
            FROM billing_profiles
            WHERE user_id = ?
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($autoInvoice, $rfc, $name, $postalCode, $regime, $cfdiUse, $billingEmail);
        $stmt->fetch();
        $stmt->close();

        if ($autoInvoice == 1) {

            // ✅ obtener template de factura
            $stmt = $conn->prepare("
                SELECT subject, body 
                FROM email_templates 
                WHERE code = 'invoice_request' 
                LIMIT 1
            ");
            $stmt->execute();
            $stmt->bind_result($invSubject, $invTemplate);
            $stmt->fetch();
            $stmt->close();

            // ✅ variables
            $vars = [
                'user_id' => $userId,
                'purchase_id' => $orderId,
                'date' => date('d/m/Y H:i'),
                'rfc' => $rfc,
                'name' => $name,
                'postal_code' => $postalCode,
                'regime' => $regime,
                'cfdi_use' => $cfdiUse,
                'email' => $billingEmail
            ];

            // ✅ al tener facturacion automatica ocn API esto se elimina
            $invoiceBody = renderTemplate($invTemplate, $vars);

            // ✅ enviar correo a soporte
            sendEmail(
                "support@addendafacil.com",
                $invSubject,
                $invoiceBody
            );

            // ✅ OPCIONAL: guardar solicitud automáticamente
            $stmt = $conn->prepare("
                INSERT INTO invoice_requests (user_id, purchase_id)
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $userId, $orderId);
            $stmt->execute();
            $stmt->close();
        }

    echo "OK";
    exit;
}

// ✅ ignorar otros eventos
echo "Evento ignorado";