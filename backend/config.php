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
define('PAYPAL_ENV', 'sandbox'); // 'sandbox' o 'live'
define('PAYPAL_CLIENT_ID', 'AVuWRRuwCRhBVVWf7rvlv64erEU5-QxolBokEGVheOK88MwuENfaqGNVW16qEUCuybpb9Cc3IXoakZCn');
define('PAYPAL_SECRET', 'ECSIr-XrOxwzE7Mt2vPFawHNIokUEYMD9QW43S8IOYI10AUerZvi7NKKfej7MSDnLjiH-AgiyJESyoiY');