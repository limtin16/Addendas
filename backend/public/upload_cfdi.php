<?php

require_once __DIR__ . '/../src/Exceptions/InvalidCfdiException.php';
require_once __DIR__ . '/../src/DTO/CfdiMap.php';
require_once __DIR__ . '/../src/Services/CFDIParserService.php';

use App\Services\CFDIParserService;
use App\Exceptions\InvalidCfdiException;

echo "<pre>";

if (!isset($_FILES['cfdi'])) {
    echo "❌ No se recibió ningún archivo.";
    exit;
}

$file = $_FILES['cfdi'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo "❌ Error al subir el archivo.";
    exit;
}

$xmlContent = file_get_contents($file['tmp_name']);

$parser = new CFDIParserService();

try {
    $cfdiMap = $parser->parse($xmlContent);

    echo "✅ CFDI parseado correctamente\n\n";
    print_r($cfdiMap->toArray());

} catch (InvalidCfdiException $e) {
    echo "❌ Error CFDI: " . $e->getMessage();
} catch (Exception $e) {
    echo "❌ Error inesperado: " . $e->getMessage();
}

echo "</pre>";