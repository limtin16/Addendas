<?php
session_start();
require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión");
}

if (!isset($_SESSION['addenda_instance']['structure'])) {
    die("No hay estructura");
}

$name = $_POST['name'] ?? 'Template sin nombre';
$userId = $_SESSION['user_id'];

$structure = json_encode($_SESSION['addenda_instance']['structure']);
$xmlTemplate = $_SESSION['addenda_instance']['addenda_xml_template'];

$stmt = $conn->prepare("
    INSERT INTO templates (user_id, name, structure, xml_template)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param("isss", $userId, $name, $structure, $xmlTemplate);
$stmt->execute();

header("Location: /addendas/frontend/templates_list.php");
exit;