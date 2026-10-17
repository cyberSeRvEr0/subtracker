<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'settings.php';
require_once 'db.php';
require_once 'phpmailer/PHPMailer.php';
require_once 'phpmailer/SMTP.php';
require_once 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;   

$ADMIN_PASSWORD = 'ptracesigsegv';

$email = $_GET['email'] ?? '';
$pass = $_GET['pass'] ?? '';

if ($pass !== $ADMIN_PASSWORD) {
    die("Access denied. Usage: admin_delete.php?email=user@example.com&pass=YOUR_PASSWORD");
}

if (empty($email)) {
    die("No email provided.");
}

$stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    die("No user found with that email. Already deleted?");
}

// Delete their subscriptions first (foreign key cleanup)
$stmt = $pdo->prepare("DELETE FROM subscriptions WHERE user_id = ?");
$stmt->execute([$user['id']]);

// Delete their notifications
$stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
$stmt->execute([$user['id']]);

// Delete the user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user['id']]);

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
    $mail->addAddress($email);
    $mail->Subject = 'Your SubTracker account has been deleted';
    $mail->Body    = "Hi " . $user['name'] . ",\n\n"
                  . "Your account and all associated data have been permanently deleted.\n\n"
                  . "If you need anything else, reply to this email.\n\n"
                  . "— SubTracker";
    $mail->send();
    echo "✅ Done. " . $user['name'] . " deleted. Confirmation email sent to " . $email . ".";
} catch (Exception $e) {
    echo "✅ User deleted. ❌ Email failed: " . $e->getMessage();
}   