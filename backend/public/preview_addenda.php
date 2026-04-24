<?php
session_start();

require_once dirname(__DIR__) . '/config.php';
require_once BACKEND_ROOT . '/src/Services/CFDIParserService.php';
require_once BACKEND_ROOT . '/src/Services/CfdiValueResolver.php';

use App\Services\CFDIParserService;
use App\Services\CfdiValueResolver;

/* =======================================================
   Validaciones básicas
   ======================================================= */

if (!isset($_SESSION['addenda_instance']['addenda_xml_template'])) {
    http_response_code(400);
    echo 'No addenda template';
    exit;
}

if (!isset($_SESSION['original_cfdi_xml'])) {
    http_response_code(400);
    echo 'No CFDI loaded';
    exit;
}

/* =======================================================
   Preparar datos base
   ======================================================= */

// Addenda ORIGINAL como texto
$xml = $_SESSION['addenda_instance']['addenda_xml_template'];

// Valores enviados desde la UI
$values = json_decode(file_get_contents('php://input'), true) ?? [];

// Preparar resolver CFDI
$parser = new CFDIParserService();
$cfdiMap = $parser->parse($_SESSION['original_cfdi_xml']);
$resolver = new CfdiValueResolver($cfdiMap);

/* =======================================================
   Reemplazar atributos en Addenda
   ======================================================= */

foreach ($values as $path => $data) {

    // Solo nos interesan atributos (.@)
    if (!str_contains($path, '.@')) {
        continue;
    }

    [, $attr] = explode('.@', $path, 2);

    $value  = $data['value']  ?? null;
    $source = $data['source'] ?? null;

    // Si hay source, resolver desde CFDI
    if ($source) {
        try {
            $resolved = $resolver->resolve($source);
            if ($resolved !== null && $resolved !== '') {
                $value = $resolved;
            }
        } catch (\Throwable $e) {
            // Si no se puede resolver, conservamos valor manual
        }
    }

    if ($value === null) {
        continue;
    }

    // Reemplazo tolerante de atributo (1 sola ocurrencia)
    $xml = preg_replace(
        '/\b' . preg_quote($attr, '/') . '="[^"]*"/',
        $attr . '="' . htmlspecialchars((string)$value, ENT_XML1) . '"',
        $xml,
        1
    );
}

/* =======================================================
   Output de preview
   ======================================================= */

header('Content-Type: application/xml; charset=UTF-8');
echo $xml;
exit;