<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
session_start();
session_destroy();

header("Location: <?= $base ?>/frontend/login.php");
exit;