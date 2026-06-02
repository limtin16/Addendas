<?php
require_once __DIR__ . '/../backend/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comprar Créditos</title>

 <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">

<!-- ✅ SCRIPT CONEKTA Temporalmente desactivado-->
<!--
<script src="https://pay.conekta.com/v1.0/js/components.js"></script>
-->
<!--
<script src="https://www.paypal.com/sdk/js?client-id=Acr83GJ-rP4viuOFLb5FWzOQW7wHINpbF1nk1Z2LTe2CS93s6Kiqoi6CBxCjW4SY7cPBliyqkY_Y4x9Q&currency=MXN&locale=es_MX
"></script>
-->
<script src="https://www.paypal.com/sdk/js?client-id=AVuWRRuwCRhBVVWf7rvlv64erEU5-QxolBokEGVheOK88MwuENfaqGNVW16qEUCuybpb9Cc3IXoakZCn&currency=MXN&locale=es_MX
"></script>
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'no_credits'): ?>
            <div class="login-error">
                ❌ No tienes créditos disponibles para continuar
            </div>
        <?php endif; ?>

        <div class="header-center">
            <h2>💳 Comprar Créditos</h2>
            <p class="note">
                Adquiere paquetes de addendas para generar CFDIs de forma rápida.
            </p>
        </div>

        <!-- ✅ CONTENEDOR CHECKOUT -->
        <div id="conektaIframeContainer" style="margin-bottom:30px;"></div>

        <div class="plans-grid">

        <?php
        $plans = [
            ['credits'=>1,   'price'=>100,   'expires'=>'1 mes'],
            ['credits'=>10,  'price'=>850,   'expires'=>'3 meses'],
            ['credits'=>20,  'price'=>1500,  'expires'=>'6 meses'],
            ['credits'=>50,  'price'=>3250,  'expires'=>'6 meses'],
            ['credits'=>100, 'price'=>5500,  'expires'=>'1 año'],
            ['credits'=>200, 'price'=>10000, 'expires'=>'1 año'],
            ['credits'=>300, 'price'=>13500, 'expires'=>'1 año'],
            ['credits'=>500, 'price'=>20000, 'expires'=>'1 año'],
        ];
        foreach ($plans as $p):
            $unit = round($p['price'] / $p['credits']);
        ?>
        <div class="plan-card">
            <h3><?= $p['credits'] ?> Addenda<?= $p['credits'] > 1 ? 's' : '' ?></h3>
            <?php
                $iva = $p['price'] * 0.16;
                $total = $p['price'] + $iva;
            ?>

            <div>
                $<?= number_format($p['price'],2) ?> + IVA
            </div>

            <div style="font-size:13px; color:#6b7280;">
                Total: $<?= number_format($total,2) ?>
            </div>

            <div class="plan-expiration">
                ⏳ Expira en <?= $p['expires'] ?>
            </div>

            <div class="payment-options">

            <!-- ✅ PAYPAL -->
            <div class="paypal-button-container"
                data-credits="<?= $p['credits'] ?>"
                data-amount="<?= $total ?>">
            </div>

            <!-- ✅ CONEKTA (DESACTIVADO TEMPORALMENTE) -->
            <!--
            <button class="generate-checkout btn blue"
                    data-credits="<?= $p['credits'] ?>">
                Pagar con tarjeta / OXXO
            </button>
            -->

        </div>
        </div>

        <?php endforeach; ?>

        </div>

    </div>
</div>

<!-- ✅ SCRIPT CORRECTO -->
<script>

console.log("SCRIPT CARGADO ✅");

// ✅ ESPERAR A QUE EL DOM EXISTA
document.addEventListener("DOMContentLoaded", function () {

     // ✅ PAYPAL
    const containers = document.querySelectorAll('.paypal-button-container');

    containers.forEach(container => {

        const userId = <?= (int) $_SESSION['user_id'] ?>;
        const credits = parseInt(container.dataset.credits);
        const amount = container.dataset.amount;

        paypal.Buttons({

            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: amount
                        },
                        custom_id: JSON.stringify({
                            user_id: userId,
                            credits: credits
                        })
                    }]
                });
            },

            onApprove: async function(data, actions) {
                await actions.order.capture();

                alert("✅ Pago recibido, procesando...");

                window.location.href = "<?= BASE_URL ?>/frontend/dashboard.php?paid=1";
            }

        }).render(container);

    });

    /*

    const buttons = document.querySelectorAll('.generate-checkout');

    buttons.forEach(btn => {

        btn.addEventListener('click', async function () {

            const credits = btn.dataset.credits;

            const res = await fetch('<?= BASE_URL ?>/backend/public/create_checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ credits })
            });

            const data = await res.json();

            console.log("CHECKOUT:", data);

            // ✅ OCULTAR el botón actual
            btn.style.display = "none";

            // ✅ CREAR botón Conekta EN EL MISMO LUGAR
            const conektaBtn = document.createElement("conekta-button");

            conektaBtn.setAttribute("checkoutId", data.checkoutId);
            conektaBtn.setAttribute("locale", "es");
            conektaBtn.setAttribute("size", "large");
            conektaBtn.setAttribute("border", "rounded");

            // ✅ INSERTARLO justo donde estaba el botón original
            btn.parentNode.appendChild(conektaBtn);
        });
    });
    */
});
/*
//Conekta temporalmente desactivado
function renderCheckout(checkoutId) {

    console.log("Renderizando checkout:", checkoutId);

    const container = document.getElementById("conektaIframeContainer");
    container.innerHTML = "";

    // ✅ VALIDAR QUE CONEKTA EXISTA
    if (!window.ConektaCheckoutComponents) {
        alert("Error: Conekta no cargó");
        console.log(window);
        return;
    }

    // ✅ AQUÍ VA Integration (LO QUE NO SABÍAS DÓNDE PONER)
    window.ConektaCheckoutComponents.Integration({
        targetIFrame: "#conektaIframeContainer",
        checkoutRequestId: checkoutId,

        publicKey: "key_Ecl1wgHTgAdmj1CEw4CF2A0", // ✅ tu public key

        paymentMethods: ["Card", "Cash"],

        options: {
            theme: 'blue'
        }
    });
}
*/

if (window.location.search.includes('error')) {
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>

</body>
</html>