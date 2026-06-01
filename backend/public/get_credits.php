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
$creditsPath = $path . "src/Services/CreditService.php";
require_once $creditsPath;
require_once $dbPath;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'ok' => false,
        'error' => 'No autorizado'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

$creditService = new CreditService($conn);
$credits = $creditService->getAvailableCredits($userId);

echo json_encode([
    'ok' => true,
    'credits' => $credits
]);