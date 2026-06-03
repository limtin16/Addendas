<?php
session_start();

/* =======================================================
   1. Validaciones básicas
   ======================================================= */
   
if (!isset($_FILES['cfdi']) || $_FILES['cfdi']['error'] !== UPLOAD_ERR_OK) {
    die('❌ Debes subir un CFDI válido');
}

$originalCfdi = file_get_contents($_FILES['cfdi']['tmp_name']);

if (!$originalCfdi || trim($originalCfdi) === '') {
    die('❌ El CFDI está vacío');
}
if (!isset($_POST['addenda_xml']) || trim($_POST['addenda_xml']) === '') {
    die('❌ No se recibió la Addenda generada.');
}

/* =======================================================
   2. Cargar CFDI desde form (si viene)
   ======================================================= */
$originalCfdi = file_get_contents($_FILES['cfdi']['tmp_name']);

if (!$originalCfdi || trim($originalCfdi) === '') {
    die('❌ El CFDI está vacío');
}

// =======================================
// ✅ DETECTAR NAMESPACE cfdi DINÁMICAMENTE
// =======================================

$cfdiNamespace = 'http://www.sat.gob.mx/cfd/4'; // fallback

if (preg_match('/xmlns:cfdi="([^"]+)"/', $originalCfdi, $matches)) {
    $cfdiNamespace = $matches[1];
}

if (trim($originalCfdi) === '') {
    die('❌ El CFDI original está vacío en sesión');
}

$newAddendaXml = trim($_POST['addenda_xml']);


/* =======================================================
   3. Detectar formato del CFDI original
   ======================================================= */

$newline = str_contains($originalCfdi, "\r\n") ? "\r\n" : "\n";

if (preg_match('/\n([ \t]+)<cfdi:/', $originalCfdi, $m)) {
    $baseIndent = $m[1];
} else {
    $baseIndent = "  ";
}

/* =======================================================
   5. Insertar Addenda sin tocar el resto del CFDI
   ======================================================= */
$doc = new DOMDocument();
$doc->loadXML($originalCfdi);

// ✅ usar fragmento XML
$fragment = $doc->createDocumentFragment();

if (!$fragment->appendXML($newAddendaXml)) {
    die('❌ Error al insertar XML de addenda');
}

// obtener comprobante
$xpath = new DOMXPath($doc);
$xpath->registerNamespace('cfdi', $cfdiNamespace);

$comprobante = $xpath->query('//cfdi:Comprobante')->item(0);

if (!$comprobante) {
    die('❌ No se encontró Comprobante');
}

// insertar correctamente
$comprobante->appendChild($fragment);

$finalCfdi = $doc->saveXML();

/* =======================================================
   6. Salida del CFDI final
   ======================================================= */

header('Content-Type: application/xml; charset=UTF-8');
header('Content-Disposition: attachment; filename="cfdi_con_addenda.xml"');

echo $finalCfdi;
exit;