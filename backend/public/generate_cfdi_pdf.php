<?php
ob_start();

// ============================
// ✅ PATHS
// ============================
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
    $path.="../";
}

$dbPath = $path . "backend/db.php";
$alPath = $path . "backend/helpers/dompdf/autoload.inc.php";
$path= $path . "backend/config.php";

require_once $path;
require_once $dbPath;
require_once $alPath;

use Dompdf\Dompdf;

function money($n) {
    return number_format((float)$n, 2);
}
// ============================
// ✅ GET ID
// ============================
$id = $_GET['id'] ?? null;

$stmt = $conn->prepare("SELECT xml, filename FROM generated_cfdis WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("CFDI no encontrado");
}

$xml = $row['xml'];
$filename = $row['filename'];

// ============================
// ✅ PARSE XML
// ============================
$xmlObj = simplexml_load_string($xml);

// ✅ namespaces
$namespaces = $xmlObj->getNamespaces(true);

// ✅ detectar namespace CFDI
$cfdiNs = $namespaces['cfdi'] ?? $namespaces[''] ?? null;
$cfdi = $cfdiNs ? $xmlObj->children($cfdiNs) : $xmlObj;

// ============================
// ✅ NODOS
// ============================
$emisor = $cfdi->Emisor ?? null;
$receptor = $cfdi->Receptor ?? null;
$conceptos = $cfdi->Conceptos->Concepto ?? [];

// ✅ atributos correctos
$emisorAttr = $emisor ? $emisor->attributes() : null;
$receptorAttr = $receptor ? $receptor->attributes() : null;
$rootAttr = $xmlObj->attributes();

// ✅ valores
$rfcEmisor = (string)($emisorAttr['Rfc'] ?? '');
$nombreEmisor = (string)($emisorAttr['Nombre'] ?? '');
$rfcReceptor = (string)($receptorAttr['Rfc'] ?? '');
$nombreReceptor = (string)($receptorAttr['Nombre'] ?? '');
$total = (string)($rootAttr['Total'] ?? '0');
$serie = (string)($rootAttr['Serie'] ?? '');
$folio = (string)($rootAttr['Folio'] ?? '');
$fecha = (string)($rootAttr['Fecha'] ?? '');
$metodoPago = (string)($rootAttr['MetodoPago'] ?? '');
$formaPago = (string)($rootAttr['FormaPago'] ?? '');
$moneda = (string)($rootAttr['Moneda'] ?? '');

$regimenEmisor = (string)($emisorAttr['RegimenFiscal'] ?? '');
$usoCfdi = (string)($receptorAttr['UsoCFDI'] ?? '');
$cpReceptor = (string)($receptorAttr['DomicilioFiscalReceptor'] ?? '');

$tfd = $xmlObj->xpath('//*[local-name()="TimbreFiscalDigital"]')[0] ?? null;

$tfdAttr = $tfd ? $tfd->attributes() : [];

$fechaTimbrado = (string)($tfdAttr['FechaTimbrado'] ?? '');
$noCertSAT = (string)($tfdAttr['NoCertificadoSAT'] ?? '');
$selloSAT = (string)($tfdAttr['SelloSAT'] ?? '');
$selloCFD = (string)($tfdAttr['SelloCFD'] ?? '');
$rfcProvCert = (string)($tfdAttr['RfcProvCertif'] ?? '');

$impuestos = $cfdi->Impuestos ?? null;

$taxNode = $xmlObj->xpath('//*[local-name()="Impuestos"]')[0] ?? null;

$totalImpuestos = '0';

if ($taxNode) {
    $attr = $taxNode->attributes();
    $totalImpuestos = (string)($attr['TotalImpuestosTrasladados'] ?? '0');
}

// ============================
// ✅ UUID (TIMBRE)
// ============================
$uuid = '';

// ✅ acceder a Complemento sin namespace
foreach ($xmlObj->xpath('//*[local-name()="TimbreFiscalDigital"]') as $tfdNode) {

    $attr = $tfdNode->attributes();

    if ($attr && isset($attr['UUID'])) {
        $uuid = (string)$attr['UUID'];
        break;
    }
}

// ============================
// ✅ QR
// ============================
$totalFormat = number_format((float)$total,6,'.','');
$qrData = "?re=".$rfcEmisor."&rr=".$rfcReceptor."&tt=".$totalFormat."&id=".$uuid;
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
$qrData = "?re=".$rfcEmisor."&rr=".$rfcReceptor."&tt=".$totalFormat."&id=".$uuid;
// ============================
// ✅ QR (SIN ARCHIVO)
// ============================

/*
// ============================
// ✅ QR (DESACTIVADO TEMPORALMENTE)
// ============================

$qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $qrApi);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$qrImage = curl_exec($ch);

if ($qrImage === false) {
    die('Error generando QR: ' . curl_error($ch));
}

curl_close($ch);

// ✅ convertir a base64 directamente
$qrBase64 = base64_encode($qrImage);
*/

$qrBase64 = '';

// ============================
// ✅ HTML
// ============================
$html = '
<style>
body { font-family: Arial; font-size: 11px; }
.header { display:flex; justify-content:space-between; }
.box { border:1px solid #000; padding:8px; margin-bottom:10px; }
.small { font-size:9px; }
table { width:100%; border-collapse: collapse; }
th, td { border:1px solid #000; padding:4px; font-size:10px; }
th { background:#eee; }
</style>

<h2 style="text-align:center;">Factura Electrónica (CFDI 4.0)</h2>

<table width="100%" border="1" cellpadding="4">
<tr>
    <td width="60%">
        <b>'.$nombreEmisor.'</b><br>
        RFC: '.$rfcEmisor.'
    </td>
    <td width="40%">
        Serie: '.$serie.'<br>
        Folio: '.$folio.'<br>
        Fecha: '.$fecha.'
    </td>
</tr>
</table>

<table width="100%" border="1" cellpadding="4">
<tr>
<td>
Régimen fiscal: '.$regimenEmisor.'<br>
Tipo comprobante: I<br>
Exportación: 01
</td>
</tr>
</table>

<table width="100%" border="1" cellpadding="4">
<tr>
<td>
RFC receptor: '.$rfcReceptor.'<br>
Nombre receptor: '.$nombreReceptor.'<br>
CP receptor: '.$cpReceptor.'<br>
Uso CFDI: '.$usoCfdi.'
</td>
</tr>
</table>

<table width="100%" border="1" cellpadding="4">
<tr>
<th>Clave</th>
<th>Cant</th>
<th>Unidad</th>
<th>Descripción</th>
<th>V.Unit</th>
<th>Importe</th>
</tr>
';

foreach ($conceptos as $c) {
    $a = $c->attributes();

    $html .= '<tr>
        <td>'.$a['ClaveProdServ'].'</td>
        <td>'.$a['Cantidad'].'</td>
        <td>'.$a['ClaveUnidad'].'</td>
        <td>'.$a['Descripcion'].'</td>
        <td>'.number_format((float)$a['ValorUnitario'], 2).'</td>
        <td>'.number_format((float)$a['Importe'], 2).'</td>
    </tr>';
}

$html .= '
<tr>
<td colspan="6">
IVA 16%: '.$totalImpuestos.'
</td>
</tr>';

$html .= '
<table width="100%" border="1" class="small">
<tr><td>UUID: '.$uuid.'</td></tr>
<tr><td>Fecha timbrado: '.$fechaTimbrado.'</td></tr>
<tr><td>RFC PAC: '.$rfcProvCert.'</td></tr>
</table>';


$html .= '
<div class="small">
<b>Sello CFDI:</b><br>'.$selloCFD.'<br><br>
<b>Sello SAT:</b><br>'.$selloSAT.'
</div>';

// ============================
// ✅ ADDENDA
// ============================
$addendaNodes = $xmlObj->xpath('//*[local-name()="Addenda"]');

if (!empty($addendaNodes)) {

    $addendaXml = $addendaNodes[0]->asXML();

    $html .= '
    <div class="box small">
        <b>Addenda</b><br>
        <pre>'.htmlspecialchars($addendaXml).'</pre>
    </div>';
}

// ============================
// ✅ QR IMAGE
// ============================
$html .= '
<div class="section">
    <b>QR SAT</b><br>
    <img src="data:image/png;base64,'.$qrBase64.'" />
</div>';

// ============================
// ✅ PDF
// ============================
if (ob_get_length()) ob_end_clean();

$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

if (ob_get_length()) ob_end_clean();

// ✅ quitar .xml si existe
$filename = preg_replace('/\.xml$/i', '', $filename);

// ✅ asegurar extensión pdf
$filename .= '.pdf';

$dompdf->stream($filename, ["Attachment" => true]);
exit;