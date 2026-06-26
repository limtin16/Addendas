<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/helpers/mailer.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$cfdiId = intval($data['cfdi_id'] ?? 0);

if (!$email || !$cfdiId) {
    echo json_encode(["success" => false]);
    exit;
}

// ✅ obtener token
$stmt = $conn->prepare("
    SELECT token FROM generated_cfdis WHERE id = ?
");
$stmt->bind_param("i", $cfdiId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["success" => false]);
    exit;
}

$token = $row['token'];

// ✅ URL descarga
$downloadUrl = BASE_URL_FULL . "/backend/public/recover_cfdi.php?id=" . $cfdiId;

// ✅ email
$subject = "✅ Tu CFDI está listo para descargar";

$body = "
<div style='font-family:Arial;'>

    <h2>📄 CFDI generado correctamente</h2>

    <p>Puedes descargar tu CFDI en cualquier momento con este enlace:</p>

    <p style='text-align:center; margin:30px 0;'>
        {$downloadUrl}
           ⬇ Descargar CFDI
        </a>
    </p>

    <p style='color:#555;'>
        ⚠️ Este enlace estará disponible durante <b>7 días</b>.
    </p>

</div>
";

sendEmail($email, $subject, $body);

echo json_encode(["success" => true]);