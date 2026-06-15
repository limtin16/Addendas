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
require_once dirname(__DIR__) . '/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    header("Location: " . BASE_URL . "/frontend/register.php?error=missing");
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashedPassword);
    $stmt->execute();

    header("Location: " . BASE_URL . "/frontend/login.php?success=1");
    exit;

} catch (mysqli_sql_exception $e) {

    // Código 1062 = duplicado
    if ($e->getCode() == 1062) {
        header("Location: " . BASE_URL . "/frontend/register.php?error=duplicate");
    } else {
        header("Location: " . BASE_URL . "/frontend/register.php?error=general");
    }
    exit;
}