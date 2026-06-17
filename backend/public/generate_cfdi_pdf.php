<?php
ob_start();

// ============================
// ✅ PATHS
// ============================
$path="";
$count=(substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if($count==0){
    $count=(substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for($i=0;$i<$count;$i++){ $path.="../"; }

require_once $path."backend/config.php";
require_once $path."backend/db.php";

// ✅ DOMPDF
require_once $path."backend/helpers/dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$generatePDF = true;

// ============================
// ✅ FUNCIONES
// ============================
function generarCadenaOriginal($xmlString) {
    try {
        libxml_use_internal_errors(true);

        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($xmlString);

        $xslPath = __DIR__ . "/../xslt/cadenaoriginal_4_0.xslt";

        $xslDoc = new DOMDocument();
        $xslDoc->load($xslPath);

        $proc = new XSLTProcessor();

        if (method_exists($proc, 'setSecurityPrefs')) {
            $proc->setSecurityPrefs(XSL_SECPREF_NONE);
        }

        $proc->importStylesheet($xslDoc);

        $cadena = $proc->transformToXML($xmlDoc);

        return trim($cadena);

    } catch (Throwable $e) {
        return 'Error cadena';
    }
}

function money($n){ return number_format((float)$n,2); }

// ============================
// ✅ DATA
// ============================
$id=$_GET['id'];

$stmt=$conn->prepare("SELECT xml, filename FROM generated_cfdis WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$row=$stmt->get_result()->fetch_assoc();

$xmlObj=simplexml_load_string($row['xml']);
$ns=$xmlObj->getNamespaces(true);
$cfdi=$ns['cfdi']?$xmlObj->children($ns['cfdi']):$xmlObj;

$em=$cfdi->Emisor->attributes();
$re=$cfdi->Receptor->attributes();
$root=$xmlObj->attributes();

$tfd=$xmlObj->xpath('//*[local-name()="TimbreFiscalDigital"]')[0]->attributes();
$Impuestos=$cfdi->Impuestos->Traslados->Traslado->attributes();

$rfcEmisor=(string)$em['Rfc'];
$nombreEmisor=(string)$em['Nombre'];

$rfcReceptor=(string)$re['Rfc'];
$nombreReceptor=(string)$re['Nombre'];

$serie=(string)$root['Serie'];
$folio=(string)$root['Folio'];
$fecha=(string)$root['Fecha'];

$total=(float)$root['Total'];
$subtotal=(float)$root['SubTotal'];

$uuid=(string)$tfd['UUID'];
$cadenaOriginal = generarCadenaOriginal($row['xml']);

// ============================
// ✅ QR
// ============================
$totalFormat = rtrim(rtrim(number_format($total, 6, '.', ''), '0'), '.');
$fe = substr((string)$tfd['SelloCFD'], -8);

$qrData = "https://verificacfdi.facturaelectronica.sat.gob.mx/" .
    "?id=".$uuid.
    "&re=".$rfcEmisor.
    "&rr=".$rfcReceptor.
    "&tt=".$totalFormat.
    "&fe=".$fe;

$qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);

$qrImage = file_get_contents($qrApi);
$qrBase64 = base64_encode($qrImage);

// ============================
// ✅ HTML (TU MISMO)
// ============================
$html = '
<style>
body{ font-family:Arial; font-size:14px; }
table{ width:100%; border-collapse:collapse; }
</style>

<h2>CFDI</h2>

<b>Emisor:</b> '.$nombreEmisor.' ('.$rfcEmisor.')<br>
<b>Receptor:</b> '.$nombreReceptor.' ('.$rfcReceptor.')<br>
<b>Folio:</b> '.$folio.'<br>
<b>Fecha:</b> '.$fecha.'<br>

<hr>

<b>Subtotal:</b> $'.money($subtotal).'<br>
<b>Total:</b> $'.money($total).'<br>

<hr>

<b>UUID:</b> '.$uuid.'<br>

data:image/png;base64,'.$qrBase64.'

<hr>

<b>Cadena original:</b><br>
<div style="word-break:break-all; font-size:10px;">
'.$cadenaOriginal.'
</div>
';

// ============================
// ✅ OUTPUT CONTROL
// ============================
if (!$generatePDF) {
    echo $html;
    exit;
}

if (ob_get_length()) ob_end_clean();

// ============================
// ✅ DOMPDF
// ============================
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$htmlFinal = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>
<body>
'.$html.'
</body>
</html>
';

$dompdf->loadHtml($htmlFinal, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ============================
// ✅ OUTPUT
// ============================
$filename = preg_replace('/\.xml$/i', '', $row['filename']) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');

echo $dompdf->output();
exit;