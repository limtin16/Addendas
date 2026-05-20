<?php

$host = 'localhost';
$db   = 'addendas';   // 👈 ESTA ES LA BASE QUE CREASTE
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}