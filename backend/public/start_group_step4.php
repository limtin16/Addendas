<?php
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
session_start();
// ✅ construir grupo
$group = [
    'type' => 'group',
    'name' => $_POST['group_name'],
    'item_name' => $_POST['item_name'],
    'repeatable' => true,
    'source' => $_POST['source'] ?? null,
    'children' => []
];

// ✅ convertir a JSON
$encodedGroup = urlencode(json_encode($group));

// ✅ redirect con grupo en URL
header(
    "Location: " . BASE_URL .
    "/frontend/wizard_step4.php?template_id=" .
    urlencode($_POST['template_id']) .
    "&current_group=" . $encodedGroup
);

exit;