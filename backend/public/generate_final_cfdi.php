<?php
session_start();

/* =======================================================
   1. Validaciones básicas
   ======================================================= */

if (!isset($_SESSION['original_cfdi_xml'])) {
    die('❌ No se encontró el CFDI original en sesión.');
}

if (!isset($_POST['addenda_xml']) || trim($_POST['addenda_xml']) === '') {
    die('❌ No se recibió la Addenda generada.');
}

/* =======================================================
   2. Cargar CFDI desde form (si viene)
   ======================================================= */

if (isset($_FILES['cfdi']) && $_FILES['cfdi']['error'] === UPLOAD_ERR_OK) {

    $xml = file_get_contents($_FILES['cfdi']['tmp_name']);

    if (!$xml || trim($xml) === '') {
        die('❌ El CFDI subido está vacío');
    }

    $_SESSION['original_cfdi_xml'] = $xml;
}

$originalCfdi = $_SESSION['original_cfdi_xml'];

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
   4. Preparar Addenda respetando el formato original
   ======================================================= */

$addendaText = str_replace(["\r\n", "\r", "\n"], $newline, $newAddendaXml);

$addendaIndented = preg_replace(
    '/^/m',
    $baseIndent,
    $addendaText
);

$addendaIndented = $newline . rtrim($addendaIndented, "\r\n");

// ✅ ENVOLVER EN cfdi:Addenda
$addendaWrapped =
    $newline .
    $baseIndent . '<cfdi:Addenda xmlns:cfdi="' . $cfdiNamespace . '">'.
    $addendaIndented .
    $newline .
    $baseIndent . '</cfdi:Addenda>' .
    $newline;

/* =======================================================
   5. Insertar Addenda sin tocar el resto del CFDI
   ======================================================= */

if (strpos($originalCfdi, '</cfdi:Complemento>') !== false) {

    $finalCfdi = str_replace(
        '</cfdi:Complemento>',
        "</cfdi:Complemento>{$addendaWrapped}",
        $originalCfdi
    );

} elseif (strpos($originalCfdi, '</cfdi:Comprobante>') !== false) {

    $finalCfdi = str_replace(
        '</cfdi:Comprobante>',
        "{$addendaWrapped}</cfdi:Comprobante>",
        $originalCfdi
    );

} else {

    // ✅ Caso: comprobante autocerrado
    if (preg_match('/<cfdi:Comprobante[^>]*\/>/', $originalCfdi)) {

        $finalCfdi = preg_replace(
            '/(<cfdi:Comprobante[^>]*?)\/>/',
            "$1>{$addendaWrapped}</cfdi:Comprobante>",
            $originalCfdi
        );

    } else {
        die('❌ No se pudo insertar la Addenda: formato de CFDI desconocido');
    }
}

/* =======================================================
   6. Salida del CFDI final
   ======================================================= */

header('Content-Type: application/xml; charset=UTF-8');
header('Content-Disposition: attachment; filename="cfdi_con_addenda.xml"');

echo $finalCfdi;
exit;