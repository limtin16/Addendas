<?php

// ✅ detectar si estás en localhost
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    $host = 'localhost';
    $db   = 'addendas';   // 👈 ESTA ES LA BASE QUE CREASTE
    $user = 'root';
    $pass = '';
} else {
    $host = 'localhost';
    $db   = 'desenti2_addendas';   // 👈 ESTA ES LA BASE QUE CREASTE
    $user = 'desenti2_af_admin';
    $pass = 'Dani1687';
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}