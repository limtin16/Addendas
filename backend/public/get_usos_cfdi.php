<?php
require_once '../db.php';

$regime = $_GET['regime'] ?? null;

$stmt = $conn->prepare("
    SELECT u.code, u.description
    FROM sat_uso_cfdi u
    JOIN sat_regimen_uso r ON u.code = r.uso_cfdi_code
    WHERE r.regimen_code = ?
");

$stmt->bind_param("s", $regime);
$stmt->execute();

echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));