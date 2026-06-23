<?php
// ✅ SIEMPRE lo primero
header('Content-Type: application/json');

// ✅ evita basura antes del JSON
ob_clean();

$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$paypalHelperPath = $path . "backend/helpers/paypal.php";
$path.="backend/config.php";
require_once $path;
require_once $paypalHelperPath;

session_start();

try {
    $redirect = $_GET['redirect'] ?? BASE_URL . "/frontend/select_mode.php";

    // ✅ cálculo
    $price = 115;
    $iva = $price * 0.16;
    $total = round($price + $iva, 2);

    // ✅ metadata
    $custom = json_encode([
        "type" => "guest_addenda",
        "redirect" => $redirect
    ]);

    $order = createPayPalOrder([
        "amount" => $total,
        "currency" => "MXN",
        "custom_id" => $custom
    ]);

    if (!$order || !isset($order->id)) {
        http_response_code(500);
        echo json_encode([
            "error" => "No se pudo crear la orden",
            "debug" => $order
        ]);
        exit;
    }

    echo json_encode([
        "id" => $order->id
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Exception",
        "message" => $e->getMessage()
    ]);
}
exit;