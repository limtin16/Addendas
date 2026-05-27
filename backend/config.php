<?php

// ✅ detectar base automáticamente
$script = $_SERVER['SCRIPT_NAME'];
echo "script". $script;
if (strpos($script, '/addendas/') === 0) {
    $base = '/addendas';
} else {
    $base = 'www.addendasfacil.com';
}
echo "BASE: " . $base;
define('BASE_URL', $base);
define('BACKEND_ROOT', __DIR__);
define('TEMPLATE_STORAGE_PATH', BACKEND_ROOT . '/Storage/templates');