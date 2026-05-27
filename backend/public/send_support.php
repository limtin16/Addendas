<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido");
}

$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$email || !$message) {
    die("Datos incompletos");
}

// ✅ tu correo destino
$to = "tu_correo@gmail.com"; // 👈 CAMBIA ESTO

$subject = "🆘 Nuevo ticket de soporte - Addendas";

// ✅ contenido del correo
$body = "
Has recibido una nueva solicitud de soporte:

Correo del usuario: $email

Mensaje:
$message

-------------------------
Fecha: " . date('Y-m-d H:i:s') . "
IP: " . $_SERVER['REMOTE_ADDR'] . "
";

// ✅ headers básicos
$headers = "From: soporte@tuapp.com\r\n";
$headers .= "Reply-To: $email\r\n";

// ✅ enviar
if (mail($to, $subject, $body, $headers)) {
    echo "<h3>✅ Tu mensaje fue enviado correctamente</h3>";
    echo "<a href='/addendas/frontend/select_mode.php'>Volver</a>";
} else {
    echo "❌ Error enviando el mensaje";
}