<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ timezone
date_default_timezone_set('America/Mexico_City');

// ✅ debug inicial
file_put_contents(__DIR__ . "/cron_debug.txt", "SCRIPT EJECUTADO: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// ✅ paths
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

// ✅ fechas controladas por PHP (FIX CRÍTICO)
$now = date('Y-m-d H:i:s');
$in3days = date('Y-m-d H:i:s', strtotime('+3 days'));

// ✅ obtener template
$stmtTpl = $conn->prepare("
    SELECT subject, body 
    FROM email_templates 
    WHERE code = 'credits_expiring' 
    LIMIT 1
");
$stmtTpl->execute();
$stmtTpl->bind_result($subject, $templateBody);
$stmtTpl->fetch();
$stmtTpl->close();

// ✅ QUERY PRINCIPAL
$stmt = $conn->prepare("
    SELECT b.user_id, b.remaining_credits, b.expires_at, u.email
    FROM user_credit_batches b
    JOIN users u ON u.id = b.user_id
    WHERE b.remaining_credits > 0
    AND b.expires_at BETWEEN ? AND ?
");

$stmt->bind_param("ss", $now, $in3days);
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
        LIMIT 1
    ");
    $check->bind_param("is", $userId, $today);
    $check->execute();
    $check->bind_result($existsId);
    $check->fetch();
    $check->close();

    if ($existsId) {
        $skippedCount++;
        echo "⏭ Usuario $userId ya notificado hoy\n";
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

        $sentCount++;
        echo "✅ Email enviado a: $email\n";

        // ✅ log
        $log = $conn->prepare("
            INSERT INTO email_logs (user_id, email, template_code, status)
            VALUES (?, ?, 'credits_expiring', 'sent')
        ");
        $log->bind_param("is", $userId, $email);
        $log->execute();
        $log->close();

    } catch (Exception $e) {

        $errorCount++;
        $errors[] = "User $userId: " . $e->getMessage();

        file_put_contents(
            __DIR__ . "/cron_error.txt",
            "User $userId: " . $e->getMessage() . "\n",
            FILE_APPEND
        );

        echo "❌ Error con usuario $userId\n";
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