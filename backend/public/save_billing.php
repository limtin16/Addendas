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
require_once dirname(__DIR__) . '../db.php';

$userId = $_SESSION['user_id'] ?? null;

$rfc = $_POST['rfc'];
$name = $_POST['name'];
$postal = $_POST['postal_code'];
$regime = $_POST['regime'];
$cfdi = $_POST['cfdi_use'];
$email = $_POST['email'];
$auto = isset($_POST['auto_invoice']) ? 1 : 0;

// ✅ verificar si ya existe perfil
$stmt = $conn->prepare("SELECT id FROM billing_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();

if ($existing) {

    // ✅ UPDATE
    $stmt = $conn->prepare("
        UPDATE billing_profiles
        SET email=?, rfc=?, name=?, postal_code=?, regime=?, cfdi_use=?, auto_invoice=?
        WHERE user_id=?
    ");

    $stmt->bind_param(
        "ssssssii",
        $email,
        $rfc,
        $name,
        $postal,
        $regime,
        $cfdi,
        $auto,
        $userId
    );

} else {

    // ✅ INSERT
    $stmt = $conn->prepare("
        INSERT INTO billing_profiles
        (user_id, email, rfc, name, postal_code, regime, cfdi_use, auto_invoice)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issssssi",
        $userId,
        $email,
        $rfc,
        $name,
        $postal,
        $regime,
        $cfdi,
        $auto
    );
}

// ✅ ejecutar (para ambos casos)
$stmt->execute();

header("Location: " . BASE_URL . "/frontend/billing.php");
exit;