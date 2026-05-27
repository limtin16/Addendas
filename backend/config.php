<?php

// ✅ detectar si estás en localhost
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    define('BASE_URL', '/addendas');
} else {
    define('BASE_URL', '');
}

define('BACKEND_ROOT', __DIR__);
define('TEMPLATE_STORAGE_PATH', BACKEND_ROOT . '/Storage/templates');