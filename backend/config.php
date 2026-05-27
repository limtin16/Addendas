<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
define('BACKEND_ROOT', __DIR__);
define('TEMPLATE_STORAGE_PATH', BACKEND_ROOT . '/src/Storage/templates');
define('FRONTEND_URL', '<?= $base ?>');
