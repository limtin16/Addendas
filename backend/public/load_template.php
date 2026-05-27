<?php
session_start();
require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/src/Services/AddendaXmlBuilder.php';

// ✅ validar login
if (!isset($_SESSION['user_id'])) {
    die("No autorizado");
}

$id = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// ✅ obtener template
$stmt = $conn->prepare("
    SELECT structure, xml_template 
    FROM templates 
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param("ii", $id, $userId);
$stmt->execute();

$result = $stmt->get_result();
$template = $result->fetch_assoc();

if (!$template) {
    die("Template no encontrado");
}

// ✅ USAR DIRECTAMENTE EL XML original guardado
$structure = json_decode($template['structure'], true);
$xmlTemplate = $template['xml_template'];

if (!$xmlTemplate) {
    die("❌ Template sin XML (debe re-guardarse)");
}

// guardar todo
$_SESSION['addenda_instance'] = [
    'structure' => $structure,
    'addenda_xml_template' => $xmlTemplate
];

$_SESSION['using_template'] = true;

// ✅ redirigir al formulario
header("Location: /frontend/render_instance_form.php");
exit;