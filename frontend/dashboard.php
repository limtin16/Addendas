<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /addendas/frontend/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

    <style>
        body {
            font-family: 'Segoe UI', Arial;
            margin: 0;
            background: #f4f6f9;
        }

        /* HEADER */
        .header {
            background: #1e293b;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .logout {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        /* CONTENEDOR */
        .container {
            padding: 40px;
            max-width: 1200px;
            margin: auto;
            text-align: center;
        }

        /* TEXTO */
        .welcome {
            margin-bottom: 40px;
        }

        .welcome h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }

        .welcome p {
            color: #666;
        }

        /* 🔥 GRID CORRECTO (CENTRADO REAL) */
        .cards {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
        }

        /* TARJETAS */
        .card {
            background: white;
            padding: 30px 25px;
            border-radius: 12px;
            width: 300px; /* 🔥 tamaño fijo */
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            transition: all 0.25s ease;
            text-align: center;
        }

        .card:hover {
            transform: translateY(-6px);
        }

        /* CONTENIDO */
        .card h3 {
            margin-top: 0;
            font-size: 18px;
        }

        .card p {
            font-size: 14px;
            color: #666;
        }

        /* BOTONES */
        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }

        .blue { background: #007bff; }
        .green { background: #28a745; }
        .gray { background: #6c757d; }
    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <h1>📄 Addenda Generator</h1>

    <a href="/addendas/frontend/logout.php" class="logout">
        Cerrar sesión
</div>

<!-- CONTENIDO -->
<div class="container">

    <div class="welcome">
        <h2>Bienvenido 👋</h2>
        <p>Selecciona una acción para comenzar</p>
    </div>

    <div class="cards">

        <!-- CREAR ADDENDA -->
        <div class="card">
            <h3>🆕 Crear Addenda</h3>
            <p>Genera una nueva addenda desde cero</p>
            <a href="/addendas/frontend/select_mode.php" class="btn blue">
                Crear
            </a>
        </div>

        <!-- TEMPLATES -->
        <div class="card">
            <h3>📁 Mis Templates</h3>
            <p>Reutiliza templates guardados previamente</p>
            <a href="/addendas/frontend/templates_list.php" class="btn gray">
                Ver templates
            </a>
        </div>

        <!-- CFDIs GENERADOS -->
        <div class="card">
            <h3>📑 CFDIs generados</h3>
            <p>Consulta y descarga CFDIs generados anteriormente</p>
            <a href="/addendas/frontend/cfdi_list.php" class="btn gray">
                Ver historial
            </a>
        </div>

    </div>

</div>

</body>
</html>