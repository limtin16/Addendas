<?php
session_start();

// ✅ registrar que tiene 1 crédito
$_SESSION['guest_credits'] = 1;
$_SESSION['guest_paid'] = TRUE;

$redirect = $_GET['redirect'] ?? "/frontend/select_mode.php";

header("Location: " . $redirect);
exit;