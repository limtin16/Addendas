<?php

// ✅ rutas internas (como ya lo tienes)
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    define('BASE_URL', '/addendas');
} else {
    define('BASE_URL', '');
}

// ✅ NUEVO: dominio completo SOLO para correos
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    define('BASE_URL_FULL', 'http://localhost/addendas');
} else {
    define('BASE_URL_FULL', 'https://addendafacil.com');
}

define('BACKEND_ROOT', __DIR__);
define('TEMPLATE_STORAGE_PATH', BACKEND_ROOT . '/Storage/templates');