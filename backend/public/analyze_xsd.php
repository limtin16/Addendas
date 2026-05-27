<?php
session_start();


require_once dirname(__DIR__) . '/config.php';
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
header('Location: /frontend/render_instance_form.php');
exit;