<?php

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

$newAddendaXml = trim($_POST['addenda_xml']);

/* =======================================================
   2. Detectar namespace cfdi dinámicamente
   ======================================================= */

if (!$originalCfdi || trim($originalCfdi) === '') {
    die('❌ El CFDI está vacío');
}

// =======================================
// ✅ DETECTAR NAMESPACE cfdi DINÁMICAMENTE
// =======================================

$doc = new DOMDocument();
$doc->loadXML($originalCfdi);

$cfdiNamespace = $doc->lookupNamespaceURI('cfdi') ?: '';

/* =======================================================
   3. Insertar Addenda con DOM
   ======================================================= */

libxml_use_internal_errors(true);

$doc = new DOMDocument();
if (!$doc->loadXML($originalCfdi)) {
    die('❌ XML CFDI inválido');
}

// ✅ crear fragmento
$fragment = $doc->createDocumentFragment();

if (!$fragment->appendXML($newAddendaXml)) {
    die('❌ Error al insertar XML de addenda');
}

// xpath
$xpath = new DOMXPath($doc);
$xpath->registerNamespace('cfdi', $cfdiNamespace);

$comprobante = $xpath->query('//cfdi:Comprobante')->item(0);

if (!$comprobante) {
    die('❌ No se encontró Comprobante');
}

// ✅ insertar
$comprobante->appendChild($fragment);

$finalCfdi = $doc->saveXML();

/* =======================================================
   4. Salida
   ======================================================= */

header('Content-Type: application/xml; charset=UTF-8');
header('Content-Disposition: attachment; filename="cfdi_con_addenda.xml"');

echo $finalCfdi;
exit;