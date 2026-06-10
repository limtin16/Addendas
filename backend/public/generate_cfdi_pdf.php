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

// ✅ VARIABLES
$rfcEmisor=(string)$em['Rfc'];
$nombreEmisor=(string)$em['Nombre'];
$regimenEmisor=(string)$em['RegimenFiscal'];

$rfcReceptor=(string)$re['Rfc'];
$nombreReceptor=(string)$re['Nombre'];
$usoCfdi=(string)$re['UsoCFDI'];
$cpReceptor=(string)$re['DomicilioFiscalReceptor'];

$serie=(string)$root['Serie'];
$folio=(string)$root['Folio'];
$fecha=(string)$root['Fecha'];
$metodoPago=(string)$root['MetodoPago'];
$formaPago=(string)$root['FormaPago'];
$moneda=(string)$root['Moneda'];

$total=(float)$root['Total'];
$subtotal=(float)$root['SubTotal'];

$taxNode=$xmlObj->xpath('//*[local-name()="Impuestos"]')[0]??null;
$totalImpuestos=$taxNode?(float)$taxNode->attributes()['TotalImpuestosTrasladados']:0;

$uuid=(string)$tfd['UUID'];
$fechaTimbrado=(string)$tfd['FechaTimbrado'];
$rfcProvCert=(string)$tfd['RfcProvCertif'];
$noCertSAT=(string)$tfd['NoCertificadoSAT'];
$selloSAT=(string)$tfd['SelloSAT'];
$selloCFD=(string)$tfd['SelloCFD'];

$cadena=$xmlObj->xpath('//*[local-name()="TimbreFiscalDigital"]')[0]->asXML();

// ✅ QR (DESACTIVADO)
$qrBase64='';

// ============================
// ✅ HTML
// ============================
// ============================
// ✅ CATÁLOGO RÉGIMEN
// ============================
$regimenMap = [
    '601'=>'General de Ley Personas Morales',
    '626'=>'Régimen Simplificado de Confianza'
];

$regEmisorDesc = $regimenMap[$regimenEmisor] ?? $regimenEmisor;
$regReceptorDesc = $regimenMap[$receptor['RegimenFiscal'] ?? ''] ?? '';

// ============================
// ✅ HTML FINAL
// ============================
$html='
<style>
body{
    font-family:Arial;
    font-size:10px;
    line-height:1.5;
    background:#e6e6e6;
}

.pdf-container{
    width:850px;
    margin:auto;
    background:#fff;
    padding:20px;
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
    padding:6px;
}

.concept-table th{
    background:#d9d9d9;
}

.text-block{
    text-align:justify;
    word-break:break-all;
}

.small{ font-size:8px; }

.right{ text-align:right; }

.concept-table td{
    vertical-align:middle;
}

.concept-table tr{
    height:28px; /* control altura uniforme */
}

/* SOLO quitar bordes en filas específicas */
.no-border td:nth-child(6),
.no-border td:nth-child(7),
.no-border td:nth-child(8),
.no-border td:nth-child(9){
    border:none;
}
.concept-table th:nth-child(5),
.concept-table th:nth-child(6){
    border:none;
}
    .concept-table tr{
    height:28px;  /* controla altura uniforme */
}

.text-block{
    text-align:justify;
    word-break:break-all;
    line-height:1.4;
}
    .concept-table th,
.concept-table td{
    font-size:9px;
    padding:5px;
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
    .concept-table td{
    vertical-align:middle;
}
    .concept-table td,
.concept-table th{
    text-align:center;
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
<td>Ingreso</td>
</tr>

<tr>
<td><b>Régimen fiscal:</b></td>
<td>'.$regEmisorDesc.'</td>
</tr>

<tr>
<td><b>Exportación:</b></td>
<td>No aplica</td>
</tr>

</table>

</td>

</tr>
</table>

<!-- CONCEPTOS -->
<table class="concept-table section">
<tr>
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
$html .= '
<tr>
<td>'.$a['ClaveProdServ'].'</td>
<td>'.($a['NoIdentificacion'] ?? '').'</td>
<td>'.$a['Cantidad'].'</td>
<td>'.$a['ClaveUnidad'].'</td>
<td>'.($a['Unidad'] ?? '').'</td>
<td class="right">'.money($a['ValorUnitario']).'</td>
<td class="right">'.money($a['Importe']).'</td>
<td class="right">'.money($a['Descuento'] ?? 0).'</td>
<td>Sí objeto de impuesto</td>
</tr>

<!-- FILA DESCRIPCIÓN + IMPUESTOS (MISMA FILA) -->
<tr>
<td style="background:#d9d9d9; white-space:nowrap;"><b>Descripción</b></td>
<td colspan="4">'.$a['Descripcion'].'</td>
<td colspan="4" style="border:none; vertical-align:top;">

<table style="width:100%; border:none; font-size:9px;">
<tr>

<td style="border:none;" class="right">
<b>Impuesto</b><br>IVA
</td>

<td style="border:none;" class="right">
<b>Tipo</b><br>Traslado
</td>

<td style="border:none;" class="right">
<b>Base</b><br>'.money($a['Importe']).'
</td>

<td style="border:none;" class="right">
<b>Tasa</b><br>16%
</td>

<td style="border:none;" class="right">
<b>Importe</b><br>'.money($totalImpuestos).'
</td>

</tr>
</table>

</td>

</tr>

<!-- FILA PEDIMENTO / CUENTA -->
<tr>
<td style="background:#d9d9d9;"><b>Número de pedimento</b></td>
<td></td>
<td style="background:#d9d9d9;"><b>Número de cuenta predial</b></td>
<td></td>
<td style="border:none;" colspan="5"></td>
</tr>
';
}

$html.='
<table class="section" style="width:100%; margin-top:15px; border-collapse:collapse;">

<tr>

<!-- ✅ MONEDA -->
<td style="border:none; width:20%;"><b>Moneda:</b></td>
<td style="border:none; width:30%;">Peso Mexicano</td>

<!-- ✅ SUBTOTAL -->
<td style="border:none; width:20%;"><b>Subtotal</b></td>
<td style="border:none; width:30%; text-align:right;">
$ '.money($subtotal).'
</td>

</tr>

<tr>

<!-- ✅ FORMA PAGO -->
<td style="border:none;"><b>Forma de pago:</b></td>
<td style="border:none;">'.$formaPago.'</td>

<!-- ✅ IMPUESTOS -->
<td style="white-space:nowrap;">
<b>Impuestos trasladados IVA 16%</b>
</td>
<td style="border:none; text-align:right;">
$ '.money($totalImpuestos).'
</td>

</tr>

<tr>

<!-- ✅ METODO PAGO -->
<td style="border:none;"><b>Método de pago:</b></td>
<td style="border:none;">'.$metodoPago.'</td>

<!-- ✅ TOTAL -->
<td style="border:none;"><b>Total</b></td>
<td style="border:none; text-align:right; font-weight:bold;">
$ '.money($total).'
</td>

</tr>

</table>

</td>

</tr>
</table>

<!-- SELLOS -->
<div class="section small text-block">
<b>Sello digital del CFDI:</b><br>
'.$selloCFD.'<br><br>

<b>Sello digital del SAT:</b><br>
'.$selloSAT.'
</div>

<!-- CADENA -->
<div class="section small text-block">
<b>Cadena original del complemento de certificación digital del SAT:</b><br>
'.$cadena.'
</div>

<!-- SAT INFO -->
<table class="section small">
<tr>
<td><b>RFC del proveedor de certificación:</b> '.$rfcProvCert.'</td>
<td><b>Fecha certificación:</b> '.$fechaTimbrado.'</td>
</tr>
<tr>
<td><b>No certificado SAT:</b> '.$noCertSAT.'</td>
<td></td>
</tr>
</table>

<!-- FOOTER -->
<div class="section small">
Este documento es una representación impresa de un CFDI<br>
El logotipo de esta factura es responsabilidad única y exclusiva de quien la emite, en consecuencia,<br>
el SAT queda relevado de cualquier obligación que derive de ello.<br>
Página 1 de 1
</div>
';

// ✅ DEBUG HTML
echo $html;
exit;