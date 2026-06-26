<?php
require_once "../db.php";

$ids = array_filter(array_map('intval', explode(',', $_GET['ids'] ?? '')));

if (!$ids) die("Sin datos");

$zip = new ZipArchive();
$tmp = tempnam(sys_get_temp_dir(), 'zip');
$zip->open($tmp, ZipArchive::CREATE);

$idList = implode(',', $ids);

$result = $conn->query("
    SELECT id, filename, xml, original_name
    FROM generated_cfdis 
    WHERE id IN ($idList)
");

while ($row = $result->fetch_assoc()) {
    $baseName = $row['original_name']
    ? pathinfo($row['original_name'], PATHINFO_FILENAME)
    : 'cfdi_' . $row['id'];

    $zip->addFromString(
        $baseName . "_cfdi_" . $row['id'] . ".xml",
        $row['xml']
    );
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="cfdis.zip"');

readfile($tmp);
unlink($tmp);