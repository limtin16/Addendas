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

session_start();

$redirect = $_GET['redirect'] ?? BASE_URL . "/frontend/select_mode.php";

// ✅ AGREGAR ESTO
$price = 115;
$iva = $price * 0.16;
$total = round($price + $iva, 2);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagar Addenda</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<script src="https://www.paypal.com/sdk/js?client-id=<?= PAYPAL_CLIENT_ID ?>&currency=MXN&locale=es_MX"></script>
</head>
<body>
<!-- ✅ SIDEBAR -->
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<div class="main">
    <div class="container form-centered">

        <div class="card">

            <h2>💳 Pago requerido</h2>

            <p class="note">
                Para continuar necesitas pagar una addenda.
            </p>

            <!-- ✅ WARNING -->
            <div class="warning-box">
                ⚠️ Si te registras obtienes mejores precios, paquetes de créditos y beneficios adicionales.
                <a href="<?= BASE_URL ?>/frontend/register.php" class="btn gray full">
                    👉 Registrarme y pagar menos
                </a>
            </div>
            

            <!-- ✅ RESUMEN -->
            <div class="plan-summary">
                <b>1 Addenda</b><br>
                Precio: <b>$115 MXN</b>
            </div>

            <div id="paypal-button-container" style="margin-top:20px;"></div>

        </div>

    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const container = document.getElementById("paypal-button-container");

    if (!container) {
        console.error("No existe #paypal-button-container");
        return;
    }

    paypal.Buttons({

        createOrder: function(data, actions) {

            const total = <?= json_encode($total) ?>;

            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: total
                    },
                    custom_id: JSON.stringify({
                        type: "guest_addenda",
                        redirect: <?= json_encode($redirect) ?>
                    })
                }]
            });
        },

        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {

                window.location.href =
                    "<?= BASE_URL ?>/frontend/check_guest_access.php"
                    + "?orderID=" + data.orderID
                    + "&redirect=<?= urlencode($redirect) ?>";
            });
        },

        onError: function(err) {
            console.error(err);
            alert("Error en el pago");
        }

    }).render('#paypal-button-container');

});
</script>

</body>
</html>