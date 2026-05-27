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
$path.="backend/config.php";
require_once $path;
require_once BACKEND_ROOT . '/src/Services/XsdToArrayConverter.php';
require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\AddendaXmlBuilder;
use App\Services\XsdToArrayConverter;

if (!isset($_FILES['xsd'])) {
    die('No se subió archivo XSD');
}

$xsd = file_get_contents($_FILES['xsd']['tmp_name']);

$converter = new XsdToArrayConverter();
$structure = $converter->convert($xsd);

$builder = new AddendaXmlBuilder();

// ✅ generar XML base desde estructura (FIX)
$addendaXmlTemplate = $builder->build([
    'children' => [$structure], // ✅ forzar modo XSD
]);

// ✅ guardar igual que flujo XML
$_SESSION['addenda_instance'] = [
    'structure' => $structure,
    'addenda_xml_template' => $addendaXmlTemplate,
    'origin' => 'xsd'
];

// ✅ redirigir
header('Location: " . BASE_URL . "/frontend/render_instance_form.php');
exit;