<?php
session_start();
session_destroy();

header("Location: /addendas/frontend/login.php");
exit;