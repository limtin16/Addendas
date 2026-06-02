<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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
$result = $stmt->get_result();

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

while ($row = $result->fetch_assoc()) {

    $userId = $row['user_id'];

    // ✅ evitar duplicados
    $check = $conn->prepare("
        SELECT id FROM email_logs 
        WHERE user_id = ? 
        AND template_code = 'credits_expiring'
        AND DATE(sent_at) = ?
    ");
    $check->bind_param("is", $userId, $today);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if ($exists) {
        $skippedCount++;
        echo "⏭ Usuario $userId ya notificado hoy\n";
        continue;
    }

    try {

        $vars = [
            'remaining' => $row['remaining_credits'],
            'expires_at' => date('d/m/Y', strtotime($row['expires_at'])),
            'dashboard_url' => BASE_URL_FULL . "/frontend/dashboard.php"
        ];

        $body = renderTemplate($templateBody, $vars);

        // ✅ enviar correo
        $sent = sendEmail($row['email'], $subject, $body);

        if ($sent) {
            $sentCount++;
            echo "✅ Email enviado a: " . $row['email'] . "\n";

            $log = $conn->prepare("
                INSERT INTO email_logs (user_id, email, template_code, status)
                VALUES (?, ?, 'credits_expiring', 'sent')
            ");
            $log->bind_param("is", $userId, $row['email']);
            $log->execute();
            $log->close();

        } else {
            throw new Exception("Error al enviar correo");
        }

    } catch (Exception $e) {
        $errorCount++;
        $errors[] = "User $userId: " . $e->getMessage();

        echo "❌ Error con usuario $userId: " . $e->getMessage() . "\n";
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