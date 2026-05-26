<?php
session_start();
require_once dirname(__DIR__) . '/db.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    die("No autorizado");
}

$name = $_POST['name'] ?? null;
$cfdiId = $_POST['cfdi_id'] ?? null;

if (!$name || !$cfdiId) {
    die("Datos incompletos");
}

// ✅ obtener XML desde BD
$stmt = $conn->prepare("
    SELECT xml 
    FROM generated_cfdis 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $cfdiId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("CFDI no encontrado");
}

$xml = $row['xml'];

// ✅ guardar template
$stmt = $conn->prepare("
    INSERT INTO templates (user_id, name, structure, xml_template, created_at) 
    VALUES (?, ?, ?, ?, NOW())
");


$structure = json_encode([
    'type' => 'cfdi_template',
    'source' => 'generated_cfdi'
]); // 👈 valor por defecto
$stmt->bind_param("isss", $userId, $name, $structure, $xml);
$stmt->execute();

// ✅ redirigir bonito
header("Location: /addendas/frontend/templates_list.php");
exit;