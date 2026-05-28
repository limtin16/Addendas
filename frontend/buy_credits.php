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

<!-- ✅ SCRIPT CONEKTA -->
<script src="https://pay.conekta.com/v1.0/js/components.js"></script>
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container">

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
            ['credits'=>1, 'price'=>100],
            ['credits'=>10, 'price'=>850],
            ['credits'=>20, 'price'=>1500],
            ['credits'=>50, 'price'=>3250],
            ['credits'=>100, 'price'=>5500],
            ['credits'=>200, 'price'=>10000],
            ['credits'=>300, 'price'=>13500],
            ['credits'=>500, 'price'=>20000],
        ];

        foreach ($plans as $p):
            $unit = round($p['price'] / $p['credits']);
        ?>

        <div class="plan-card">

            <h3><?= $p['credits'] ?> Addenda<?= $p['credits'] > 1 ? 's' : '' ?></h3>

            <div>$<?= number_format($p['price'],2) ?></div>
            <div>$<?= $unit ?> por addenda</div>

            <?php if ($p['credits'] == 1): ?>

                <button class="generate-checkout btn blue"
                        data-credits="1">
                    Pagar con tarjeta / OXXO
                </button>

            <?php else: ?>

                <button class="btn blue">
                    Comprar (demo)
                </button>

            <?php endif; ?>

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

    console.log("DOM LISTO ✅");

    const buttons = document.querySelectorAll('.generate-checkout');
    console.log("Botones encontrados:", buttons.length);

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

});

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

        publicKey: "key_test_xxxxx", // ✅ tu public key

        paymentMethods: ["Card", "Cash"],

        options: {
            theme: 'blue'
        }
    });
}

</script>

</body>
</html>