<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
session_start();
$_SESSION['guest_paid'] = true;

$redirect = $_GET['redirect'] ?? "<?= $base ?>/frontend/select_mode.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagar Addenda</title>
<link rel="stylesheet" href="<?= $base ?>/frontend/assets/styles.css">
</head>
<body>

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
                <a href="<?= $base ?>/frontend/register.php" class="btn gray full">
                    👉 Registrarme y pagar menos
                </a>
            </div>
            

            <!-- ✅ RESUMEN -->
            <div class="plan-summary">
                <b>1 Addenda</b><br>
                Precio: <b>$115 MXN</b>
            </div>

            <button id="payBtn" class="btn blue full">
                💳 Pagar $115 MXN
            </button>

        </div>

    </div>
</div>

<script>
document.getElementById('payBtn').addEventListener('click', function () {

    // ✅ simulación de pago
    alert("✅ Pago simulado exitoso");

    // ✅ redirigir a flujo original
    window.location.href = "<?= $redirect ?>";
});
</script>

</body>
</html>