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

// ============================
// ✅ GET ID
// ============================
$id = $_GET['id'] ?? null;

$stmt = $conn->prepare("SELECT xml FROM generated_cfdis WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("CFDI no encontrado");
}

$xml = $row['xml'];

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

// ============================
// ✅ HTML
// ============================
$html = '
<style>
body {
    font-family: Arial;
    font-size: 12px;
    color: #000;
}
h2 { text-align: center; }
.section { margin-bottom: 10px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #ccc; padding: 5px; }
th { background: #eee; }
</style>

<h2>Factura Electrónica (CFDI)</h2>

<div class="section">
<b>Emisor</b><br>
RFC: '.$rfcEmisor.'<br>
Nombre: '.$nombreEmisor.'
</div>

<div class="section">
<b>Receptor</b><br>
RFC: '.$rfcReceptor.'<br>
Nombre: '.$nombreReceptor.'
</div>

<div class="section">
<b>Conceptos</b>
<table>
<tr>
<th>Clave</th>
<th>Descripción</th>
<th>Cantidad</th>
<th>Importe</th>
</tr>
';

// ✅ llenar tabla
foreach ($conceptos as $c) {

    $attr = $c->attributes();

    $html .= "<tr>
        <td>".(string)$attr['ClaveProdServ']."</td>
        <td>".(string)$attr['Descripcion']."</td>
        <td>".(string)$attr['Cantidad']."</td>
        <td>".(string)$attr['Importe']."</td>
    </tr>";
}

$html .= '
</table>
</div>

<div class="section">
<b>Total:</b> $'.$total.'
</div>
';

// ============================
// ✅ ADDENDA
// ============================
if (isset($xmlObj->Addenda)) {
    $html .= '
    <div class="section">
    <b>Addenda</b><br>
    <pre style="background:#f5f5f5; padding:10px; font-size:10px;">'
    . htmlspecialchars($xmlObj->Addenda->asXML()) .
    '</pre>
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

$dompdf->stream("cfdi.pdf", ["Attachment" => true]);
exit;