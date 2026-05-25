<?php
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

$stmt = $conn->prepare("
INSERT INTO billing_profiles (user_id, email, rfc, name, postal_code, regime, cfdi_use, auto_invoice)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
email=VALUES(email),
rfc=VALUES(rfc),
name=VALUES(name),
postal_code=VALUES(postal_code),
regime=VALUES(regime),
cfdi_use=VALUES(cfdi_use),
auto_invoice=VALUES(auto_invoice)
");

$stmt->bind_param("issssssi", $userId, $email, $rfc, $name, $postal, $regime, $cfdi, $auto);
$stmt->execute();

header("Location: /addendas/frontend/billing.php");
exit;