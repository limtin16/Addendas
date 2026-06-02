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
define('PAYPAL_ENV', 'live'); // 'sandbox' o 'live'
//sandbox credentials
//define('PAYPAL_CLIENT_ID', 'AVuWRRuwCRhBVVWf7rvlv64erEU5-QxolBokEGVheOK88MwuENfaqGNVW16qEUCuybpb9Cc3IXoakZCn');
//define('PAYPAL_SECRET', 'ECSIr-XrOxwzE7Mt2vPFawHNIokUEYMD9QW43S8IOYI10AUerZvi7NKKfej7MSDnLjiH-AgiyJESyoiY');
//live credentials
define('PAYPAL_CLIENT_ID', 'Acr83GJ-rP4viuOFLb5FWzOQW7wHINpbF1nk1Z2LTe2CS93s6Kiqoi6CBxCjW4SY7cPBliyqkY_Y4x9Q');
define('PAYPAL_SECRET', 'EFKk58p-Hd7UrNbd1UykCs_rQKL3WtfSn5RlukQey3eVQ2rbbQ7jGhIoIJJUQt_j70CRA0R7Io1QYElF');