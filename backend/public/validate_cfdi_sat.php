<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

function respond($arr) {
    while (ob_get_level()) ob_end_clean();
    echo json_encode($arr);
    exit;
}

if (!isset($_FILES['cfdi'])) {
    respond(['error' => 'No file']);
}

$xmlContent = file_get_contents($_FILES['cfdi']['tmp_name']);

libxml_use_internal_errors(true);

$xml = simplexml_load_string($xmlContent);

if (!$xml) {
    respond(['error' => 'XML inválido']);
}

$ns = $xml->getNamespaces(true);

$xml->registerXPathNamespace('c', $ns['cfdi'] ?? '');
$xml->registerXPathNamespace('t', $ns['tfd'] ?? '');

try {

    $total = (string)$xml->xpath('//c:Comprobante')[0]['Total'];
    $emisor = (string)$xml->xpath('//c:Emisor')[0]['Rfc'];
    $receptor = (string)$xml->xpath('//c:Receptor')[0]['Rfc'];
    $uuid = (string)$xml->xpath('//t:TimbreFiscalDigital')[0]['UUID'];

    $total = number_format((float)$total, 6, '.', '');

    $expresion = "?re={$emisor}&rr={$receptor}&tt={$total}&id={$uuid}";

    $wsdl = "https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc?wsdl";

    $client = new SoapClient($wsdl, [
        'connection_timeout' => 8,
        'exceptions' => true
    ]);

    $res = $client->Consulta([
        'expresionImpresa' => $expresion
    ])->ConsultaResult;

    respond([
        'success' => true,
        'estado' => $res->Estado,
        'codigo' => $res->CodigoEstatus
    ]);

} catch (Throwable $e) {

    respond([
        'warning' => 'No se pudo consultar SAT'
    ]);
}