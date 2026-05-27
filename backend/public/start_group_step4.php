<?php
$path = "";
$depth = substr_count(__DIR__, DIRECTORY_SEPARATOR) - substr_count(__DIR__, DIRECTORY_SEPARATOR) + substr_count(substr(__DIR__, strpos(__DIR__, 'addendas')), DIRECTORY_SEPARATOR);
for ($i = 0; $i < $depth; $i++) {
    $path .= "../";
}
$path .= "backend/config.php";
require_once $path;

session_start();

$_SESSION['current_group'] = [
    'type' => 'group',
    'name' => $_POST['group_name'],
    'item_name' => $_POST['item_name'], // ✅ CORRECTO
    'repeatable' => true,
    'source' => $_POST['source'] ?? null,
    'children' => []
];

header('Location: " . BASE_URL . "/frontend/wizard_step4.php?template_id=' . $_POST['template_id']);
exit;
