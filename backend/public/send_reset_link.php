<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
    $path.="../";
}
$path.="backend/config.php";
require_once $path;

require_once dirname(__DIR__) . '/db.php';

/* ✅ NUEVO: helper de correo */
require_once dirname(__DIR__) . '/helpers/mailer.php';

$email = $_POST['email'] ?? '';

if (!$email) {
    die("Correo requerido");
}

// ✅ validar existencia
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    die("❌ El correo no existe");
}

// ✅ generar token
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// ✅ guardar token
$stmt = $conn->prepare("
    UPDATE users SET reset_token = ?, reset_expires = ?
    WHERE id = ?
");
$stmt->bind_param("ssi", $token, $expires, $user['id']);
$stmt->execute();

// ✅ link correcto
$link = BASE_URL . "/frontend/reset_password.php?token=" . $token;

// ✅ contenido del correo
$body = "
    <h2>🔐 Recuperar contraseña</h2>
    <p>Haz clic en el siguiente enlace:</p>
    <p><a href='$link'>Restablecer contraseña</a></p>
    <p>Este enlace expira en 1 hora.</p>
";

// ✅ USO DE LA FUNCIÓN REUTILIZABLE
$sent = sendEmail(
    $email,
    "Recuperación de contraseña",
    $body
);

// ✅ respuesta
if ($sent) {
    echo "✅ Se envió un enlace de recuperación";
} else {
    echo "❌ Error al enviar correo";
}