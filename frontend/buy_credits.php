<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /frontend/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comprar Créditos</title>
<link rel="stylesheet" href="/frontend/assets/styles.css">
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
                $isBest = in_array($p['credits'], [100, 200, 500]);
            ?>

            <div class="plan-card <?= $isBest ? 'highlight' : '' ?>">

                <?php if ($isBest): ?>
                    <div class="badge">🔥 Mejor valor</div>
                <?php endif; ?>

                <div class="plan-header">
                    <h3><?= $p['credits'] ?> Addenda<?= $p['credits'] > 1 ? 's' : '' ?></h3>
                </div>

                <div class="plan-price">
                    $<?= number_format($p['price'], 2) ?> MXN
                </div>

                <div class="plan-unit">
                    $<?= $unit ?> por addenda
                </div>

                <button class="btn blue full buy-btn"
                        data-credits="<?= $p['credits'] ?>"
                        data-price="<?= $p['price'] ?>">
                    Comprar
                </button>

            </div>

            <?php endforeach; ?>

        </div>

    </div>
</div>

<script>
// ✅ Flujo mock (sin Conekta)
document.querySelectorAll('.buy-btn').forEach(btn => {

    btn.addEventListener('click', () => {

        const credits = btn.dataset.credits;
        const price = btn.dataset.price;

        if (!confirm(`¿Comprar ${credits} addendas por $${price} MXN?`)) {
            return;
        }

        fetch('/backend/public/mock_buy_credits.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ credits })
})
.then(async r => {

    const text = await r.text();
    console.log("RESPUESTA RAW:", text);

    let res;

    try {
        res = JSON.parse(text);
    } catch (e) {
        alert("Respuesta inválida del servidor:\n" + text);
        return;
    }

    if (!res.ok) {
        alert('❌ Error al procesar la compra');
        return;
    }

    alert(`✅ Compra simulada exitosa\nCréditos agregados: ${credits}`);
})
.catch(() => {
    alert('Error de conexión');
});

    });

});
</script>

</body>
</html>