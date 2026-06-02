<?php
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

// ✅ evitar repetir envíos (opcional pero recomendado)
$today = date('Y-m-d');

// ✅ buscar créditos que expiran en <= 3 días y aún tienen saldo
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

// ✅ procesar resultados
while ($row = $result->fetch_assoc()) {

    $userId = $row['user_id'];

    // ✅ evitar duplicados (una vez por día por usuario)
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
        continue; // ya enviado hoy
    }

    // ✅ variables
    $vars = [
        'remaining' => $row['remaining_credits'],
        'expires_at' => date('d/m/Y', strtotime($row['expires_at'])),
        'dashboard_url' => BASE_URL_FULL . "/frontend/dashboard.php"
    ];

    // ✅ render template
    $body = renderTemplate($templateBody, $vars);

    // ✅ enviar correo
    sendEmail($row['email'], $subject, $body);
    /*
    // ✅ log
    $log = $conn->prepare("
        INSERT INTO email_logs (user_id, email, template_code, status)
        VALUES (?, ?, 'credits_expiring', 'sent')
    ");
    $log->bind_param("is", $userId, $row['email']);
    $log->execute();
    $log->close();
    */
}

echo "OK";