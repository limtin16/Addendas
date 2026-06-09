<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'mi_secret_key_123') {
    http_response_code(403);
    exit('No autorizado');
}

// ✅ Resolver rutas dinámicamente
$path = "";
$count = (substr_count(substr(getcwd(), strrpos(getcwd(), 'addenda'), 100), '\\'));
if ($count == 0) {
    $count = (substr_count(substr(getcwd(), strrpos(getcwd(), 'addendafacil.com'), 100), '/'));
}
for ($i = 0; $i < $count; $i++) {
    $path .= "../";
}

$dbPath = $path . "backend/db.php";
$configPath = $path . "backend/config.php";

// ✅ Incluir archivos auxiliares
require_once($dbPath);
require_once($configPath);

// ✅ Obtener todos los template_id de la BD
$query = "SELECT template_id FROM templates";
$result = $conn->query($query);

if (!$result) {
    die("Error en consulta: " . $conn->error);
}

$templateIds = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['template_id'])) {
        $templateIds[$row['template_id']] = true;
    }
}

// ✅ Configuración de tiempo (1 semana)
$now = time();
$oneWeek = 2 * 24 * 60 * 60;

// ✅ Obtener archivos JSON
$files = glob(TEMPLATE_STORAGE_PATH . '/*.json');

foreach ($files as $filePath) {

    $fileName = basename($filePath, '.json');

    // ✅ Validar antigüedad
    $fileTime = filemtime($filePath);

    if (($now - $fileTime) > $oneWeek) {

        // ✅ Validar que NO exista en DB
        if (!isset($templateIds[$fileName])) {

            // 🧪 Modo prueba (comentado unlink si quieres probar)
            //echo "Se eliminaría: {$filePath}\n";

            if (unlink($filePath)) {
                echo "✅ Eliminado: {$filePath}\n";
            } else {
                echo "❌ Error al eliminar: {$filePath}\n";
            }
                
        }
    }
}

echo "✔ Proceso finalizado\n";