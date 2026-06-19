<?php
// 🔥 BLINDAJE TOTAL
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

function respond($arr) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($arr);
    exit;
}

require_once __DIR__ . '/../helpers/sat-estado-cfdi-main/autoload.php';

use PhpCfdi\SatEstadoCfdi\Consumer;
use PhpCfdi\SatEstadoCfdi\Clients\Soap\SoapConsumerClient;

try {

    if (!isset($_FILES['cfdi'])) {
        respond(['error' => 'CFDI no recibido']);
    }

    $file = $_FILES['cfdi'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        respond(['error' => 'Error al subir CFDI']);
    }

    $xml = file_get_contents($file['tmp_name']);
    libxml_use_internal_errors(true);

    $xmlObj = @simplexml_load_string($xml);

    if (!$xmlObj) {
        respond(['error' => 'XML inválido']);
    }

    $ns = $xmlObj->getNamespaces(true);
    $cfdi = isset($ns['cfdi']) ? $xmlObj->children($ns['cfdi']) : $xmlObj;

    $em = $cfdi->Emisor->attributes();
    $re = $cfdi->Receptor->attributes();

    $tfdNode = $xmlObj->xpath('//*[local-name()="TimbreFiscalDigital"]');

    if (!$tfdNode) {
        respond(['error' => 'CFDI sin timbre']);
    }

    $tfd = $tfdNode[0]->attributes();

    $uuid = (string)$tfd['UUID'];
    //temporal
    respond([
    'error' => 'CFDI no vigente (Cancelado)'
]);
    $rfcEmisor = (string)$em['Rfc'];
    $rfcReceptor = (string)$re['Rfc'];
    $total = number_format((float)$cfdi->attributes()['Total'], 6, '.', '');

    if (!$uuid || !$rfcEmisor || !$rfcReceptor) {
        respond(['error' => 'Datos CFDI incompletos']);
    }

    // ✅ CREAR CLIENTE SOAP DE LA LIBRERÍA
    $client = new SoapConsumerClient();
    $consumer = new Consumer($client);

    // ✅ EXPRESIÓN CORRECTA (SIN &amp;)
    $expression = "?re={$rfcEmisor}&rr={$rfcReceptor}&tt={$total}&id={$uuid}";

    // ✅ CONSULTA SAT
    $cfdiStatus = $consumer->execute($expression);

    // ✅ VALIDACIÓN
    if (!$cfdiStatus->document->isActive()) {
        respond([
            'error' => 'CFDI no vigente'
        ]);
    }

    // ✅ OK
    respond([
        'success' => true,
        'estado' => 'Vigente'
    ]);

} catch (Throwable $e) {
    respond([
        'warning' => 'SAT no disponible',
        'estado' => 'DESCONOCIDO'
    ]);
}