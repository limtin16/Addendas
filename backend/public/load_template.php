<?php
session_start();
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$dbPath = $path . "backend/db.php";
$builderPath = $path . "backend/src/Services/AddendaXmlBuilder.php";
$creditServicePath = $path . "backend/src/Services/CreditService.php";
$path.="backend/config.php";
require_once $path;
require_once $dbPath;
require_once $creditServicePath;
require_once $builderPath;

session_start();

// ✅ validar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// ✅ verificar créditos
$creditService = new CreditService($conn);
$credits = $creditService->getAvailableCredits($userId);

if ($credits <= 0) {
    header("Location: " . BASE_URL . "/frontend/buy_credits.php?error=no_credits");
    exit;
}

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

$template->structure['root'] = [
    'structure' => $structure,
    'addenda_xml_template' => $xmlTemplate,
];

$_SESSION['addenda_instance'] = $template->structure;

$_SESSION['using_template'] = true;

// ✅ redirigir al formulario
header("Location: " . BASE_URL . "/frontend/render_instance_form.php");
exit;