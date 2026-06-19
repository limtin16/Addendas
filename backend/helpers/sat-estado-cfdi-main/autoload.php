<?php
spl_autoload_register(function ($class) {

    $prefix = 'PhpCfdi\\SatEstadoCfdi\\';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    //$baseDir = __DIR__ . '/../helpers/sat-estado-cfdi-main/src/';
    $baseDir = __DIR__ . '/src/';

    $relativeClass = substr($class, strlen($prefix));

    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});