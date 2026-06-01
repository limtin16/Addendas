<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$dbpath = $path;
$path.="backend/config.php";
require_once $path;
$dbpath.="backend/db.php";
require_once $dbpath;

session_start();
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: " . BASE_URL . "/frontend/login.php");
    exit;
}

// obtener datos técnicos
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// obtener versión activa
$stmt = $conn->prepare("
    SELECT version FROM privacy_policy WHERE active = 1 LIMIT 1
");
$stmt->execute();
$stmt->bind_result($version);
$stmt->fetch();
$version = $version ?: '1.0';

$stmt = $conn->prepare("
    INSERT INTO privacy_acceptance 
    (user_id, accepted_at, ip_address, user_agent, version)
    VALUES (?, NOW(), ?, ?, ?)
");
if (!$stmt) {
    die("Error SQL: " . $conn->error);
}

$stmt->bind_param("isss", $userId, $ip, $userAgent, $version);
$stmt->execute();



// redirigir al sistema
header("Location: " . BASE_URL . "/frontend/dashboard.php");
exit;