<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
file_put_contents(__DIR__ . "/cron_debug.txt", "SCRIPT EJECUTADO: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$mailerPath = $path . "backend/helpers/mailer.php";
$dbPath = $path . "backend/db.php";
$path.="backend/config.php";
require_once $path;
require_once $dbPath;
require_once $mailerPath;

echo "🔄 Iniciando proceso...\n";

$today = date('Y-m-d');

$sentCount = 0;
$skippedCount = 0;
$errorCount = 0;
$errors = [];

// ✅ buscar créditos por expirar
$stmt = $conn->prepare("
    SELECT b.user_id, b.remaining_credits, b.expires_at, u.email
    FROM user_credit_batches b
    JOIN users u ON u.id = b.user_id
    WHERE b.remaining_credits > 0
    AND b.expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)
");

$stmt->execute();
$stmt->bind_result($userId, $remainingCredits, $expiresAt, $email);

while ($stmt->fetch()) {

    file_put_contents(__DIR__ . "/cron_debug.txt", "Procesando usuario: $userId\n", FILE_APPEND);

    // ✅ evitar duplicados
    $check = $conn->prepare("
        SELECT id FROM email_logs 
        WHERE user_id = ? 
        AND template_code = 'credits_expiring'
        AND DATE(sent_at) = ?
    ");
    $check->bind_param("is", $userId, $today);
    $check->execute();
    $check->bind_result($existsId);
    $check->fetch();
    $check->close();

    if ($existsId) {
        continue;
    }

    try {

        $vars = [
            'remaining' => $remainingCredits,
            'expires_at' => date('d/m/Y', strtotime($expiresAt)),
            'dashboard_url' => BASE_URL_FULL . "/frontend/dashboard.php"
        ];

        $body = renderTemplate($templateBody, $vars);

        $sent = sendEmail($email, $subject, $body);

        if (!$sent) {
            throw new Exception("Error al enviar correo");
        }

    } catch (Exception $e) {
        file_put_contents(__DIR__ . "/cron_error.txt", $e->getMessage() . "\n", FILE_APPEND);
    }
}


// ✅ resumen final
echo "\n========================\n";
echo "📊 RESUMEN:\n";
echo "✅ Enviados: $sentCount\n";
echo "⏭ Omitidos: $skippedCount\n";
echo "❌ Errores: $errorCount\n";

if (!empty($errors)) {
    echo "\n🔴 DETALLE ERRORES:\n";
    foreach ($errors as $err) {
        echo $err . "\n";
    }
}

echo "✅ Proceso terminado\n";