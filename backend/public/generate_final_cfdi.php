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

$originalCfdi  = $_SESSION['original_cfdi_xml'];
$newAddendaXml = trim($_POST['addenda_xml']);

/* =======================================================
   2. Eliminar Addenda previa (si existiera)
   ======================================================= */

$originalCfdi = preg_replace(
    '#\s*<cfdi:Addenda\b[^>]*>.*?</cfdi:Addenda>#s',
    '',
    $originalCfdi
);

/* =======================================================
   3. Detectar formato del CFDI original
   ======================================================= */

// Detectar salto de línea (\n o \r\n)
$newline = str_contains($originalCfdi, "\r\n") ? "\r\n" : "\n";

// Detectar indentación base (espacios o tabs)
if (preg_match('/\n([ \t]+)<cfdi:/', $originalCfdi, $m)) {
    $baseIndent = $m[1];
} else {
    $baseIndent = "  "; // fallback: 2 espacios
}

/* =======================================================
   4. Preparar Addenda respetando el formato original
   ======================================================= */

// Normalizar saltos de línea de la Addenda
$addendaText = str_replace(["\r\n", "\r", "\n"], $newline, $newAddendaXml);

// Indentar la Addenda con el mismo nivel del CFDI
$addendaIndented = preg_replace(
    '/^/m',
    $baseIndent,
    $addendaText
);

// Asegurar saltos correctos alrededor
$addendaIndented = $newline . rtrim($addendaIndented, "\r\n");

/* =======================================================
   5. Insertar Addenda sin tocar el resto del CFDI
   ======================================================= */

if (strpos($originalCfdi, '</cfdi:Complemento>') !== false) {

    // Insertar Addenda justo después del Complemento
    $finalCfdi = str_replace(
        '</cfdi:Complemento>',
        "</cfdi:Complemento>{$addendaIndented}",
        $originalCfdi
    );

} else {

    // Insertar Addenda antes de cerrar el Comprobante
    $finalCfdi = str_replace(
        '</cfdi:Comprobante>',
        "{$addendaIndented}</cfdi:Comprobante>",
        $originalCfdi
    );
}

/* =======================================================
   6. Salida del CFDI final
   ======================================================= */

header('Content-Type: application/xml; charset=UTF-8');
header('Content-Disposition: attachment; filename="cfdi_con_addenda.xml"');

echo $finalCfdi;
exit;