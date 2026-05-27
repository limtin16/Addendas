<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Soporte</title>
    <link rel="stylesheet" href="<?= $base ?>/frontend/assets/styles.css">
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">
    <div class="container success-container">

        <div class="card">

            <h2>❓ Soporte</h2>

            <p class="note">
                ¿Tienes algún problema? Escríbenos y te ayudamos lo antes posible.
            </p>

            <hr>

            <form action="<?= $base ?>/backend/public/send_support.php" method="POST">

                <label><b>Tu correo</b></label><br>
                <input 
                    type="email" 
                    name="email" 
                    required 
                    placeholder="ejemplo@correo.com"
                    value="<?= $_SESSION['user_email'] ?? '' ?>"
                    style="width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px;"
                >

                <label><b>Describe tu problema</b></label><br>
                <textarea 
                    name="message" 
                    required 
                    rows="6" 
                    placeholder="Describe aquí el problema paso a paso..."
                    style="width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px;"
                ></textarea>

                <br>

                <button type="submit" class="btn blue">
                    📩 Enviar solicitud
                </button>

            </form>

            <br>

            <!-- Botón volver dinámico -->
            <?php if (!empty($_SESSION['user_id'])): ?>

                <a href="<?= $base ?>/frontend/dashboard.php" class="btn green">
                    ➡ Volver al dashboard
                </a>

            <?php else: ?>

                <a href="<?= $base ?>/frontend/select_mode.php" class="btn green">
                    🏠 Volver al inicio
                </a>

            <?php endif; ?>

        </div>

    </div>
</div>

</body>
</html>