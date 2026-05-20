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
    <title>Inicio - Addendas</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }

        .btn-new {
            background: #007bff;
        }

        .btn-new:hover {
            background: #0056b3;
        }

        .btn-templates {
            background: #28a745;
        }

        .btn-templates:hover {
            background: #1e7e34;
        }

        .logout {
            margin-top: 15px;
            font-size: 12px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>¿Qué deseas hacer?</h2>

    <a class="btn btn-new" href="/addendas/frontend/select_mode.php">
        Crear nueva Addenda
    </a>

    <a class="btn btn-templates" href="/addendas/frontend/templates_list.php">
        Usar template existente
    </a>

    <div class="logout">
        <a href="/addendas/backend/public/logout.php">Cerrar sesión</a>
    </div>

</div>

</body>
</html>