<?php
session_start();

$filename = $_SESSION['generated_cfdi_file'] ?? null;

if (!$filename) {
    die("No hay CFDI");
}

$path = dirname(__DIR__) . "/src/storage/cfdi_generated/" . $filename;

if (!file_exists($path)) {
    die("Archivo no encontrado");
}

header('Content-Type: application/xml');
header('Content-Disposition: attachment; filename="' . $filename . '"');

readfile($path);
exit;