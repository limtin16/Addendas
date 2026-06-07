<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$dbPath = $path . "backend/db.php";
$alPath.="backend/config.php";
$path.="backend/helper/dompdf/autoload.inc.php";

require_once $path;
require_once $dbPath;
require_once $alPath; // dompdf

use Dompdf\Dompdf;

$id = $_GET['id'] ?? null;

if (!$id) {
    die('ID requerido');
}

// ✅ obtener XML
$stmt = $conn->prepare("SELECT xml FROM generated_cfdis WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("CFDI no encontrado");
}

$xml = $row['xml'];

// ✅ PARSEAR XML
$xmlObj = simplexml_load_string($xml);

// ============================
// ✅ EXTRAER DATOS
// ============================
$emisor = $xmlObj->Emisor ?? null;
$receptor = $xmlObj->Receptor ?? null;
$conceptos = $xmlObj->Conceptos->Concepto ?? [];

$total = (string)$xmlObj['Total'];

$uuid = (string)$xmlObj->Complemento->TimbreFiscalDigital['UUID'];
$rfcEmisor = (string)$emisor['Rfc'];
$rfcReceptor = (string)$receptor['Rfc'];
$totalFormat = number_format((float)$xmlObj['Total'],6,'.','');

$qrUrl = "https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=?re=".$rfcEmisor."&rr=".$rfcReceptor."&tt=".$totalFormat."&id=".$uuid;

// ============================
// ✅ GENERAR HTML
// ============================
$html = '
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
h2 { text-align: center; margin-bottom: 5px; }
.section { margin-bottom: 10px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #ccc; padding: 5px; font-size: 11px; }
th { background: #eee; }
.small { font-size: 10px; }
</style>

<h2>Factura Electrónica (CFDI)</h2>

<div class="section">
<b>Emisor:</b><br>
RFC: '.$emisor['Rfc'].'<br>
Nombre: '.$emisor['Nombre'].'<br>
</div>

<div class="section">
<b>Receptor:</b><br>
RFC: '.$receptor['Rfc'].'<br>
Nombre: '.$receptor['Nombre'].'<br>
</div>

<div class="section">
<b>Conceptos</b>
<table>
<tr>
<th>Clave</th>
<th>Descripción</th>
<th>Cantidad</th>
<th>Importe</th>
</tr>';

foreach ($conceptos as $c) {
    $desc = (string)$c['Descripcion'];
    $imp = (string)$c['Importe'];
    $html .= "<tr><td>$desc</td><td>$imp</td></tr>";
}

$html .= '</table></div>';

$html .= '
<div class="section">
<b>Total:</b> $'.$total.'
</div>';

$html .= "</table>";

$html .= "<h3>Total: $total</h3>";

// ============================
// ✅ Agregar ADDENDA (🔥 IMPORTANTE)
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

$html .= '
<div class="section">
<b>QR SAT</b><br>
'.$qrUrl.'
</div>';

// ============================
// ✅ CREAR PDF
// ============================
$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

// ✅ salida descarga
$dompdf->stream("cfdi.pdf", ["Attachment" => true]);
exit;