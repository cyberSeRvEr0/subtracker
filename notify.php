<?php
require_once 'config.php';
require_once 'phpmailer/PHPMailer.php';
require_once 'phpmailer/SMTP.php';
require_once 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function create_notification($user_id, $title, $message, $type = 'info', $user_email = null, $user_name = null) {
    global $pdo;

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $message, $type]);

    // Send email if email provided
    if ($user_email) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->setFrom(MAIL_USERNAME, 'SubTracker');
            $mail->addAddress($user_email);
            $mail->Subject = "You've got a new notification in SubTracker";
            $mail->Body    = "Hi " . ($user_name ?? 'there') . ",\n\n"
                           . $title . "\n\n"
                           . $message . "\n\n"
                           . "— SubTracker";
            $mail->send();
        } catch (Exception $e) {
            // Don't block notification creation if email fails
            error_log("Notification email failed: " . $e->getMessage());
        }
    }
}

function get_unread_count($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read = 0");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}   