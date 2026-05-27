<?php

// detectar localhost
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    $host = 'localhost';
    $db   = 'addendas';
    $user = 'root';
    $pass = '';
} else {
    $host = 'localhost';
    $db   = 'desenti2_addendas';
    $user = 'desenti2_af_admin';
    $pass = 'Dani1687';
}

$conn = new mysqli($host, $user, $pass, $db);

// manejo de error claro
if ($conn->connect_error) {
    die("Error de conexión: (" . $conn->connect_errno . ") " . $conn->connect_error);
}

// charset recomendado
$conn->set_charset("utf8mb4");