<?php
session_start();
require_once __DIR__ . '/../backend/db.php';

// ✅ PROTEGER: solo usuarios logueados
if (!isset($_SESSION['user_id'])) {
    header("Location: /addendas/frontend/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// ✅ obtener templates del usuario
$stmt = $conn->prepare("
    SELECT id, name, created_at 
    FROM templates 
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$templates = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis Templates</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }

        .container {
            width: 500px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .template {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 10px;
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
            margin-top: 10px;
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

    <h2>Mis Templates</h2>

    <?php if (empty($templates)): ?>
        <p class="empty">No tienes templates guardados aún.</p>
    <?php else: ?>

        <?php foreach ($templates as $tpl): ?>
            <div class="template">
                <div class="name"><?= htmlspecialchars($tpl['name']) ?></div>
                <div class="date">Creado: <?= $tpl['created_at'] ?></div>

                <a class="btn" href="/addendas/backend/public/load_template.php?id=<?= $tpl['id'] ?>">
                    Usar template
                </a>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

        <a class="back" href="/addendas/frontend/dashboard.php">
            ⬅ Volver
        </a>

</div>

</body>
</html>