<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido");
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../helpers/mailer.php'; // ✅ NUEVO

$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$email || !$message) {
    die("Datos incompletos");
}

// ✅ correo destino soporte
$to = "support@addendafacil.com"; 

$subject = "🆘 Nuevo ticket de soporte - AddendaFácil";

// ✅ sanitizar (PRO TIP)
$emailSafe = htmlspecialchars($email);
$messageSafe = nl2br(htmlspecialchars($message));

// ✅ contenido del correo
$body = "
<h2>🆘 Nuevo ticket de soporte</h2>

<p><strong>Correo del usuario:</strong> $emailSafe</p>

<p><strong>Mensaje:</strong></p>
<p style='background:#f4f6f9;padding:10px;border-radius:6px;'>
    $messageSafe
</p>

<hr>

<p style='font-size:12px;color:#666;'>
Fecha: " . date('Y-m-d H:i:s') . "<br>
IP: " . $_SERVER['REMOTE_ADDR'] . "
</p>
";

// ✅ USAR FUNCIÓN GLOBAL
$sent = sendEmail(
    $to,
    $subject,
    $body,
    $email // ✅ reply-to
);

// ✅ respuesta
if ($sent) {
    echo "<h3>✅ Tu mensaje fue enviado correctamente</h3>";
    echo "" . BASE_URL . "/frontend/select_mode.php";
} else {
    echo "❌ Error enviando el mensaje";
}