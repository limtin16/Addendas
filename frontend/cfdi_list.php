<?php
session_start();
require_once __DIR__ . '/../backend/db.php';

// ✅ SOLO USUARIOS LOGUEADOS
if (!isset($_SESSION['user_id'])) {
    header("Location: /addendas/frontend/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// ✅ OBTENER CFDIs DEL USUARIO
$stmt = $conn->prepare("
    SELECT id, filename, created_at 
    FROM generated_cfdis 
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$cfdis = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis CFDIs</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }

        .container {
            width: 600px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .cfdi {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .name {
            font-weight: bold;
        }

        .date {
            font-size: 12px;
            color: gray;
        }

        .btn {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 10px;
            background: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn:hover {
            background: #0056b3;
        }

        .empty {
            text-align: center;
            color: gray;
        }

        .back {
            margin-top: 20px;
            display: block;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>📄 Mis CFDIs generados</h2>

    <?php if (empty($cfdis)): ?>
        <p class="empty">Aún no has generado CFDIs.</p>
    <?php else: ?>

        <?php foreach ($cfdis as $c): ?>
            <div class="cfdi">
                <div class="name"><?= htmlspecialchars($c['filename']) ?></div>
                <div class="date">Creado: <?= $c['created_at'] ?></div>

                <a href="/addendas/backend/public/download_cfdi_by_id.php?id=<?= $c['id'] ?>" class="btn">
                    Descargar
                </a>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <a href="/addendas/frontend/dashboard.php" class="btn back">
       ⬅ Volver
    </a>

</div>

</body>
</html>