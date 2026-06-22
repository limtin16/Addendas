<?php

// ✅ CONFIG SESIÓN (ANTES de headers)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
}

// ✅ HEADERS DE SEGURIDAD
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-Powered-By: none");
// 🔥 CSP
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https://www.paypal.com https://www.paypalobjects.com; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' https://www.paypal.com https://www.paypalobjects.com; frame-src https://www.paypal.com; connect-src 'self' https://www.paypal.com;");


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

define('SYSTEM_USER_ID', 4);
define('BACKEND_ROOT', __DIR__);
define('TEMPLATE_STORAGE_PATH', BACKEND_ROOT . '/src/Storage/templates');
define('PAYPAL_ENV', 'sandbox'); // 'sandbox' o 'live'
//sandbox credentials
define('PAYPAL_CLIENT_ID', 'AQB9ODdqdnBmZv3Zzqm10B1wRUqxJFmpmIBKP_aSYZHc14hjtH2h6ioOn252TWX5nwtbp8ZXhUzD1pTH');
define('PAYPAL_SECRET', 'EGdUoQ1h9yzdf8lnwvRCgrg-Zv9uZar7z9Lq2oH_jX6fyl-9kJHJbavKTSgOFRKu3a4pcXY3yjV4lxsT');
//live credentials
//define('PAYPAL_CLIENT_ID', 'Acr83GJ-rP4viuOFLb5FWzOQW7wHINpbF1nk1Z2LTe2CS93s6Kiqoi6CBxCjW4SY7cPBliyqkY_Y4x9Q');
//define('PAYPAL_SECRET', 'EFKk58p-Hd7UrNbd1UykCs_rQKL3WtfSn5RlukQey3eVQ2rbbQ7jGhIoIJJUQt_j70CRA0R7Io1QYElF');