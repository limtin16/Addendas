<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AddendaFácil</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

/* ✅ HERO MÁS COMPACTO */
.hero {
    text-align: center;
    padding: 40px 20px;
    background: #fff;
}

.hero h1 {
    font-size: 36px;
    margin-bottom: 10px;
}

.hero p {
    font-size: 16px;
    color: #555;
    max-width: 600px;
    margin: auto;
}

/* ✅ NAVBAR */
.nav {
    display: flex;
    justify-content: space-between;
    padding: 12px 30px; /* antes 20px */
    background: #fff;
    border-bottom: 1px solid #eee;
}

.nav h1 {
    font-size: 20px;
}

.nav a {
    text-decoration: none;
    color: #2563eb;
    font-weight: 600;
}

.btn {
    margin-top: 20px;
    display: inline-block;
    background: #2563eb;
    color: white;
    padding: 12px 22px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

/* ✅ FEATURES EN MISMA PANTALLA */
.features {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    padding: 30px;
    max-width: 900px;
    margin: auto;
}

.card {
    background: #fff;
    padding: 18px;
    border-radius: 10px;
    font-size: 14px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.card h3 {
    font-size: 16px;
    margin-bottom: 5px;
}

/* ✅ TODO QUEDE EN PANTALLA */
.main-block {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 80px);
    justify-content: space-between;
}

</style>
</head>

<body>

<!-- ✅ NAV -->
<div class="nav">
    <h1>AddendaFácil</h1>
    <a href="/frontend/login.php">Iniciar sesión</a>
</div>

<div class="main-block">

    <div>
        <!-- HERO -->
        <div class="hero">
            <h1>Genera addendas sin complicaciones</h1>

            <p>
                Empieza sin registrarte. Genera tu addenda en segundos
                y guarda tu información creando una cuenta.
            </p>

            <a href="/frontend/select_mode.php" class="btn">
                Generar addenda ahora
            </a>
        </div>

        <!-- FEATURES -->
        <div class="features">
            <div class="card">
                <h3>⚡ Rápido</h3>
                <p>En segundos</p>
            </div>

            <div class="card">
                <h3>🧾 Compatible</h3>
                <p>CFDI 4.0</p>
            </div>

            <div class="card">
                <h3>🔁 Reutilizable</h3>
                <p>Templates</p>
            </div>

            <div class="card">
                <h3>✅ Sin errores</h3>
                <p>Validado</p>
            </div>
        </div>
    </div>

    <!-- CTA FINAL -->
    <div style="text-align:center; padding:20px;">
        <a href="/frontend/register.php" class="btn">
            Crear cuenta gratis
        </a>
    </div>

</div>

<!-- ✅ FOOTER -->
<div class="footer">
    © <?= date('Y') ?> AddendaFácil — Todos los derechos reservados
</div>

</body>
</html>