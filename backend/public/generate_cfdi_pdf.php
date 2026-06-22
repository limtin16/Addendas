<?php
ob_start();

// ✅ PATHS
$path="";
$count=(substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if($count==0){
    $count=(substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for($i=0;$i<$count;$i++){ $path.="../"; }

require_once $path."backend/config.php";
require_once $path."backend/db.php";
$baseDir = realpath(__DIR__ . "/../../"); // ajusta niveles si es necesario
$xsltPath = $baseDir . "/backend/xslt/cadenaoriginal_4_0.xslt";
$generatePDF = TRUE; // ✅ TRUE = PDF | FALSE = HTML

function generarCadenaOriginal($xmlString) {

    try {
        libxml_use_internal_errors(true);
        // ✅ cargar XML desde string (tu DB)
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($xmlString);

        // ✅ cargar XSLT LOCAL (MUY IMPORTANTE)
        $xslPath = __DIR__ . "/../xslt/cadenaoriginal_4_0.xslt";

        if (!file_exists($xslPath)) {
            return '❌ No se encontró XSLT';
        }

        $xslDoc = new DOMDocument();
        $xslDoc->load($xslPath);

        // ✅ procesador
        $proc = new XSLTProcessor();

        // importante para includes dentro del XSLT
        if (method_exists($proc, 'setSecurityPrefs')) {
            $proc->setSecurityPrefs(XSL_SECPREF_NONE);
        }

        $proc->importStylesheet($xslDoc);

        // ✅ transformar
        $cadena = $proc->transformToXML($xmlDoc);

        return trim($cadena);
        libxml_clear_errors();

    } catch (Throwable $e) {
        return '❌ Error: '.$e->getMessage();
    }
}

function money($n){ return number_format((float)$n,2); }

// ✅ DATA
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

// ✅ VARIABLES
$rfcEmisor=(string)$em['Rfc'];
$nombreEmisor=(string)$em['Nombre'];
$regimenEmisor=(string)$em['RegimenFiscal'];

$rfcReceptor=(string)$re['Rfc'];
$nombreReceptor=(string)$re['Nombre'];
$usoCfdiCode = trim((string)$re['UsoCFDI']);
$cpReceptor=(string)$re['DomicilioFiscalReceptor'];
$regimenReceptor=(string)$re['RegimenFiscalReceptor'];

$serie=(string)$root['Serie'];
$folio=(string)$root['Folio'];
$fecha=(string)$root['Fecha'];
$moneda=(string)$root['Moneda'];
//cambiar formato
$tipoComprobante=(string)$root['TipoDeComprobante'];
$Exportacion=(string)$root['Exportacion'];
$metodoPago=(string)$root['MetodoPago'];
$formaPago=(string)$root['FormaPago'];


$total=(float)$root['Total'];
$subtotal=(float)$root['SubTotal'];

$uuid=(string)$tfd['UUID'];
$fechaTimbrado=(string)$tfd['FechaTimbrado'];
$rfcProvCert=(string)$tfd['RfcProvCertif'];
$noCertSAT=(string)$tfd['NoCertificadoSAT'];
$selloSAT=(string)$tfd['SelloSAT'];
$selloCFD=(string)$tfd['SelloCFD'];
$cadenaOriginal = generarCadenaOriginal($row['xml']);

// ✅ QR (DESACTIVADO)
$qrBase64='';

// ============================
// ✅ CATÁLOGO RÉGIMEN DESDE BD
// ============================

$regimenMap = [];

$res = $conn->query("SELECT code, description FROM sat_regimenes ORDER BY code ASC");

if ($res && $res->num_rows > 0) {
    while ($rowReg = $res->fetch_assoc()) {
        $regimenMap[$rowReg['code']] = $rowReg['description'];
    }
}

$regEmisorDesc = $regimenMap[trim($regimenEmisor)] ?? $regimenEmisor;
$regReceptorDesc = $regimenMap[$regimenReceptor] ?? $regimenReceptor;

// ============================
// ✅ CATÁLOGO USO CFDI
// ============================

$usoCfdiMap = [];

$resUso = $conn->query("SELECT code, description FROM sat_uso_cfdi");

if ($resUso && $resUso->num_rows > 0) {
    while ($rowUso = $resUso->fetch_assoc()) {
        $usoCfdiMap[trim($rowUso['code'])] = trim($rowUso['description']);
    }
}

$usoCfdi = $usoCfdiMap[$usoCfdiCode] ?? $usoCfdiCode;

$monedaMap = [];
$resMoneda = $conn->query("SELECT clave, descripcion FROM c_moneda_sat");

if ($resMoneda && $resMoneda->num_rows > 0) {
    while ($rowM = $resMoneda->fetch_assoc()) {
        $monedaMap[$rowM['clave']] = $rowM['descripcion'];
    }
}

$monedaDesc = $monedaMap[$moneda] ?? $moneda;

$metodoPagoMap = [];
$resMP = $conn->query("SELECT clave, descripcion FROM c_metodo_pago_sat");

if ($resMP && $resMP->num_rows > 0) {
    while ($rowMP = $resMP->fetch_assoc()) {
        $metodoPagoMap[$rowMP['clave']] = $rowMP['descripcion'];
    }
}

$formaPagoMap = [];
$resFP = $conn->query("SELECT clave, descripcion FROM c_forma_pago_sat");

if ($resFP && $resFP->num_rows > 0) {
    while ($rowFP = $resFP->fetch_assoc()) {
        $formaPagoMap[$rowFP['clave']] = $rowFP['descripcion'];
    }
}

$formaPagoDesc = $formaPagoMap[$formaPago] ?? $formaPago;

$metodoPagoDesc = $metodoPagoMap[$metodoPago] ?? $metodoPago;

$tipoComprobanteMap = [];
$resTC = $conn->query("SELECT clave, descripcion FROM c_tipo_comprobante_sat");

if ($resTC && $resTC->num_rows > 0) {
    while ($rowTC = $resTC->fetch_assoc()) {
        $tipoComprobanteMap[$rowTC['clave']] = $rowTC['descripcion'];
    }
}

$tipoComprobanteDesc = $tipoComprobanteMap[$tipoComprobante] ?? $tipoComprobante;

$exportacionMap = [];
$resExp = $conn->query("SELECT clave, descripcion FROM c_exportacion_sat");

if ($resExp && $resExp->num_rows > 0) {
    while ($rowExp = $resExp->fetch_assoc()) {
        $exportacionMap[$rowExp['clave']] = $rowExp['descripcion'];
    }
}

$exportacionDesc = $exportacionMap[$Exportacion] ?? $Exportacion;

$impuestoMap = [];
$resImp = $conn->query("SELECT clave_impuesto, descripcion FROM catalogo_impuestos_sat");

if ($resImp && $resImp->num_rows > 0) {
    while ($rowImp = $resImp->fetch_assoc()) {
        $impuestoMap[$rowImp['clave_impuesto']] = $rowImp['descripcion'];
    }
}

// ============================
// ✅ QR
// ============================
$totalFormat = rtrim(rtrim(number_format($total, 6, '.', ''), '0'), '.');
$fe = substr($selloCFD, -8);
$qrData = "https://verificacfdi.facturaelectronica.sat.gob.mx/" .
    "?id=".$uuid.
    "&re=".$rfcEmisor.
    "&rr=".$rfcReceptor.
    "&tt=".$totalFormat.
    "&fe=".$fe;

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
// ✅ HTML FINAL
// ============================
$html='
<style>
body{
    font-family:Arial;
    font-size:14px;
    line-height:1.5;
    background:#fff;
}

table:not(.concept-table) td,
table:not(.concept-table) th{
    font-size:14px;
}

.concept-table th{
    font-weight:bold;
    font-size:10px !important;
    text-align:center;
}

.concept-table td{
    font-size:10px !important;
}

.cfdi-string {
    font-size:13px;               /* 🔥 controla tamaño */
    line-height:1.2;
    word-break:break-all;        /* 🔥 corta correctamente */
    overflow-wrap:break-word;
}

.cfdi-title {
    font-size:12px;        /* 🔥 controla tamaño del título */
    font-weight:bold;
    margin-bottom:2px;
}

.pdf-container{
    width:850px;
    margin:auto;
    background:#fff;
    padding:20px;
    page-break-inside: avoid;
    padding-bottom: 80px;
}

table{
    width:100%;
    border-collapse:collapse;
}

td{
    padding:4px 6px;
    vertical-align:top;
}

.section{ margin-top:15px; }

.concept-table th,
.concept-table td{
    border:1px solid #000;
}

.concept-table th{
    background:#d9d9d9;
}

.text-block{
    word-break:break-all;
    text-align:justify;
    line-height:1.4;
}

.small{ font-size:9px; }

.right{ text-align:right; }

.concept-table td{
    vertical-align:middle;
    text-align:center;
}

.concept-table th:nth-child(1){ width:12%; }
.concept-table th:nth-child(2){ width:10%; }
.concept-table th:nth-child(3){ width:8%; }
.concept-table th:nth-child(4){ width:10%; }
.concept-table th:nth-child(5){ width:12%; }
.concept-table th:nth-child(6){ width:12%; }
.concept-table th:nth-child(7){ width:12%; }
.concept-table th:nth-child(8){ width:10%; }
.concept-table th:nth-child(9){ width:14%; }

.right-block {
    text-align:right;
    white-space:nowrap;
}

.inline-block {
    display:inline-block;
}
.section td{
    padding:4px 8px;
}

.section{
    margin-top:12px;
}
.section td:nth-child(3){
    width:200px;
}

.row-a td{
    height:28px;
}

.row-b td{
    height: 10px;
    overflow:hidden;
    font-size:9px;
}

.row-c td{
    height:35px;
}

.section table td{
    padding:4px;
}

.footer {
    text-align:center;
}

.section td{
    padding:3px 6px;
}

.section td b{
    white-space:nowrap;
}
tr {
    page-break-inside: avoid;
}

.concept-table tr {
    page-break-inside: avoid;
}

.row-b, .row-c {
    page-break-inside: avoid;
}

td {
    page-break-inside: avoid;
}

table {
    page-break-inside: auto;
}

tr {
    page-break-after: auto;
    page-break-before: auto;
}
body {
    overflow: visible !important;
}
.footer-fixed {
    position: fixed;
    bottom: 10mm;
    left: 20px;
    right: 20px;
    font-size: 11px;
    text-align: center;
}

.totales td,
.totales th {
    font-size:12px !important;
}
</style>

<div class="pdf-container">

<!-- HEADER -->
<table>
<tr>

<td width="50%">

<table>

<tr>
<td><b>RFC emisor:</b></td>
<td>'.$rfcEmisor.'</td>
</tr>

<tr>
<td><b>Nombre emisor:</b></td>
<td>'.$nombreEmisor.'</td>
</tr>

<tr>
<td><b>Folio:</b></td>
<td>'.$folio.'</td>
</tr>

<tr>
<td><b>RFC receptor:</b></td>
<td>'.$rfcReceptor.'</td>
</tr>

<tr>
<td><b>Nombre receptor:</b></td>
<td>'.$nombreReceptor.'</td>
</tr>

<tr>
<td><b>Código postal del receptor:</b></td>
<td>'.$cpReceptor.'</td>
</tr>

<tr>
<td><b>Régimen fiscal receptor:</b></td>
<td>'.$regReceptorDesc.'</td>
</tr>

<tr>
<td><b>Uso CFDI:</b></td>
<td>'.$usoCfdi.'</td>
</tr>

</table>

</td>

<td width="50%">

<table>

<tr>
<td><b>Folio fiscal:</b></td>
<td>'.$uuid.'</td>
</tr>

<tr>
<td><b>No. de serie del CSD:</b></td>
<td>'.$noCertSAT.'</td>
</tr>

<tr>
<td><b>Serie:</b></td>
<td>'.$serie.'</td>
</tr>

<tr>
<td><b>Fecha de emisión:</b></td>
<td>'.$fecha.'</td>
</tr>

<tr>
<td><b>Efecto de comprobante:</b></td>
<td>'.$tipoComprobanteDesc.'</td>
</tr>

<tr>
<td><b>Régimen fiscal:</b></td>
<td>'.$regEmisorDesc.'</td>
</tr>

<tr>
<td><b>Exportación:</b></td>
<td>'.$exportacionDesc.'</td>
</tr>

</table>

</td>

</tr>
</table>

<!-- CONCEPTOS -->
<table class="concept-table section">
<tr class="row-a">
<th>Clave del producto y/o servicio</th>
<th>No. identificación</th>
<th>Cantidad</th>
<th>Clave de unidad</th>
<th>Unidad</th>
<th>Valor unitario</th>
<th>Importe</th>
<th>Descuento</th>
<th>Objeto impuesto</th>
</tr>';

foreach($cfdi->Conceptos->Concepto as $c){
$a = $c->attributes();
$b = $c->Impuestos->Traslados->Traslado->attributes();
$g = isset($c->CuentaPredial) ? $c->CuentaPredial->attributes() : null;
$h = isset($c->InformacionAduanera) ? $c->InformacionAduanera->attributes() : null;
$html .= '
<tr class="row-b">
<td>'.$a['ClaveProdServ'].'</td>
<td>'.($a['NoIdentificacion'] ?? '').'</td>
<td>'.$a['Cantidad'].'</td>
<td>'.$a['ClaveUnidad'].'</td>
<td>'.($a['Unidad'] ?? '').'</td>
<td class="right">'.money($a['ValorUnitario']).'</td>
<td class="right">'.money($a['Importe']).'</td>
<td class="right">'.money($a['Descuento'] ?? 0).'</td>
<td>'.($a['ObjetoImp'] ?? '').'</td>
</tr>

<!-- FILA DESCRIPCIÓN + IMPUESTOS (MISMA FILA) -->
<tr class = "row-c">
<td style="background:#d9d9d9; white-space:nowrap;"><b>Descripción</b></td>
<td colspan="4" style="text-align:left;">
'.$a['Descripcion'].'
</td>
<td colspan="4" style="border:none; vertical-align:top;">

<table style="width:100%; border:none; font-size:9px;">
<tr>

<td style="border:none;" class="right">
<b>Impuesto</b><br>'.($impuestoMap[(string)($b['Impuesto'] ?? '')] ?? ($b['Impuesto'] ?? '')).' 
</td>

<td style="border:none;" class="right">
<b>Tipo</b><br>'.($b['TipoFactor'] ?? '').'
</td>

<td style="border:none;" class="right">
<b>Base</b><br>'.money($b['Importe']).'
</td>

<td style="border:none;" class="right">
<b>Tasa</b><br>'.(isset($b['TasaOCuota'])
    ? number_format((float)$b['TasaOCuota'] * 100, 2).'%' 
    : '').'
</td>

<td style="border:none;" class="right">
<b>Importe</b><br>'.money($Impuestos['Importe']).'
</td>

</tr>
</table>

</td>

</tr>

<!-- FILA PEDIMENTO / CUENTA -->
<tr class = "row-b">
<td colspan="2" style="background:#d9d9d9;"><b>Número de pedimento</b></td>
<td colspan="2" style="background:#d9d9d9;"><b>Número Cuenta predial</b></td>
</tr>

<tr class="row-b">
<td colspan="2">'.($g['Numero'] ?? '').'</td>
<td colspan="2">'.($h['NumeroPedimento'] ?? '').'</td>
</tr>

';
}

$html.='
<table class="section totales" style="width:100%; margin-top:15px; border-collapse:collapse;">
<tr>
<!-- ✅ MONEDA -->
<td style="border:none; width:20%;"><b>Moneda:</b></td>
<td style="border:none; width:30%;">'.$monedaDesc.'</td>

<!-- ✅ SUBTOTAL -->
<td style="border:none; width:20%;"><b>Subtotal</b></td>
<td style="border:none; width:30%; text-align:right;">
$ '.money($subtotal).'
</td>

</tr>

<tr>

<!-- ✅ FORMA PAGO -->
<td style="border:none;"><b>Forma de pago:</b></td>
<td style="border:none;">'.$formaPagoDesc.'</td>

<!-- ✅ IMPUESTOS -->
<td style="white-space:nowrap;">
<b>Impuestos trasladados </b> &nbsp;&nbsp;&nbsp '.($impuestoMap[(string)$Impuestos['Impuesto']] ?? $Impuestos['Impuesto']).' &nbsp;&nbsp '.(isset($Impuestos['TasaOCuota'])
    ? number_format((float)$Impuestos['TasaOCuota'] * 100, 2).'%' 
    : '').'
</td>
<td style="border:none; text-align:right;">
$ '.money($Impuestos['Importe']).'
</td>

</tr>

<tr>

<!-- ✅ METODO PAGO -->
<td style="border:none;"><b>Método de pago:</b></td>
<td style="border:none;">'.$metodoPagoDesc.'</td>

<!-- ✅ TOTAL -->
<td style="border:none;"><b>Total</b></td>
<td style="border:none; text-align:right; font-weight:bold;">
$ '.money($total).'
</td>

</tr>

</table>
';

// ============================
// ✅ ADDENDA
// ============================
$addendaNodes = $xmlObj->xpath('//*[local-name()="Addenda"]');

if (!empty($addendaNodes)) {

    $addendaNode = $addendaNodes[0];
    $inner = $addendaNode->children()[0]; // AddendaDCG

    $addendaTable = '<table class="section small" style="width:100%; border:none; border-collapse:collapse;">';

    $colCount = 0;
    $addendaTable .= '<tr>';

    foreach ($inner->attributes() as $key => $value) {

        // cada campo ocupa 2 columnas (label + valor)
        $addendaTable .= '
        <td style="border:none;"><b>'.$key.':</b></td>
        <td style="border:none;">'.$value.'</td>';

        $colCount++;

        // 3 campos por fila → 6 columnas
        if ($colCount % 3 == 0) {
            $addendaTable .= '</tr><tr>';
        }
    }

    // cerrar fila si quedó incompleta
    if ($colCount % 3 != 0) {
        $addendaTable .= '</tr>';
    }

    $addendaTable .= '</table>';

    $html .= '
    <br>
        <div class="section">
        <div style="text-align:center; margin-bottom:10px; font-size14px;">
            <b>Addenda</b>
        </div>
        <div style="margin:10px 0;">
        '.$addendaTable.'
        </div>

        </div>
        <br>';
        
}

$html .= '
<!-- SELLOS -->
<div class="section small">
<div class="cfdi-title">Sello digital del CFDI:</div>
<div class="cfdi-string">'.$selloCFD.'</div><br>

<div class="cfdi-title">Sello digital del SAT:</div>
<div class="cfdi-string">'.$selloSAT.'</div>
';

// ============================
// ✅ QR IMAGE
// ============================
$html .= '
<table class="section small" style="width:100%;">

<tr>

<!-- ✅ QR (IZQUIERDA) -->
<td width="25%" style="vertical-align:top; text-align:center;">
    '.(!empty($qrBase64)
        ? '<img src="data:image/png;base64,'.$qrBase64.'" alt="QR">'
        : ''
    ).'
</td>

<!-- ✅ CADENA + DATOS SAT (DERECHA) -->
<td width="75%" style="vertical-align:top;" class="text-block">

<div class="cfdi-title">Cadena original del complemento de certificación digital del SAT:</div>
<div class="cfdi-string">'.$cadenaOriginal.'</div>

<table style="width:100%; border-collapse:collapse;">
<tr>
<td><b>RFC del proveedor de certificación:</b> '.$rfcProvCert.'</td>
<td><b>Fecha certificación:</b> '.$fechaTimbrado.'</td>
</tr>
<tr>
<td><b>No certificado SAT:</b> '.$noCertSAT.'</td>
<td></td>
</tr>
</table>
</td>
</tr>
</table>

<!-- FOOTER -->
<div class="footer-fixed">
Este documento es una representación impresa de un CFDI<br>
El logotipo de esta factura es responsabilidad única y exclusiva de quien la emite, en consecuencia,<br>
el SAT queda relevado de cualquier obligación que derive de ello.<br>
Página 1 de 1
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

// ✅ nombre archivo
$filename = preg_replace('/\.xml$/i', '', $row['filename']) . '.pdf';

// ============================
// ✅ PDFShift
// ============================

// 🔴 API KEY
$apiKey = 'sk_47b03f0031a2eaf48dbe6b9fe23ea39900b2384d';

// ✅ iniciar CURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.pdfshift.io/v3/convert/pdf",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        "source" => '
        <!DOCTYPE html>
        <html>
        <head>
        <meta charset="UTF-8">
        <style>
        body { margin:0; }
        </style>
        </head>
        <body>
        '.$html.'
        </body>
        </html>
        ',

        // ✅ tamaño hoja
        "format" => "A4",

        // ✅ márgenes (formato correcto)
        "margin" => [
            "top" => "10mm",
            "bottom" => "10mm",
            "left" => "10mm",
            "right" => "10mm"
        ],

        // ✅ opciones válidas
        "use_print" => false
    ]),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-API-Key: " . $apiKey
    ],
]);

$pdf = curl_exec($ch);

// ✅ error CURL
if ($pdf === false) {
    die('Error PDFShift: ' . curl_error($ch));
}

// ✅ status HTTP
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    die("Error PDFShift (HTTP $httpCode): " . $pdf);
}

// ============================
// ✅ OUTPUT PDF
// ============================

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
echo $pdf;
exit;