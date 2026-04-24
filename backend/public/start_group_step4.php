<?php
session_start();

$_SESSION['current_group'] = [
    'type' => 'group',
    'name' => $_POST['group_name'],
    'itemName' => $_POST['item_name'],
    'repeatable' => true,
    'source' => $_POST['source'] ?? null,
    'children' => []
];

header('Location: /addendas/frontend/wizard_step4.php?template_id=' . $_POST['template_id']);
exit;