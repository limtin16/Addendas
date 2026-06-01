<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "/home/desenti2/PHPMailer/PHPMailer-master/src/PHPMailer.php";
require_once "/home/desenti2/PHPMailer/PHPMailer-master/src/SMTP.php";
require_once "/home/desenti2/PHPMailer/PHPMailer-master/src/Exception.php";

function renderTemplate($template, $vars = []) {
    foreach ($vars as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    return $template;
}

function sendEmail($to, $subject, $body, $replyTo = null) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // ✅ sin debug en producción
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "smtp.titan.email";
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        $mail->Username = "support@addendafacil.com";
        $mail->Password = "soporteaf.1";

        $mail->setFrom("support@addendafacil.com", "AddendaFácil");

        $mail->addAddress($to);

        // ✅ opcional reply-to (para soporte)
        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Subject = mb_encode_mimeheader($subject, 'UTF-8');
        $mail->Body = $body;

        $mail->send();

        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}