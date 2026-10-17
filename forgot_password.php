<?php
require_once 'auth.php';
if (is_logged_in()) header('Location: index.php');

require_once 'phpmailer/PHPMailer.php';
require_once 'phpmailer/SMTP.php';
require_once 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);

        $resetLink = APP_URL . "/password_reset.php?token=" . $token;   

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;   
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom(MAIL_USERNAME, 'SubTracker');     
            $mail->addAddress($email);
            $mail->Subject = 'Reset your SubTracker password';
            $mail->Body    = "Hi " . $user['name'] . ",\n\n"
                           . "Click the link below to reset your password:\n\n"
                           . $resetLink . "\n\n"
                           . "If you didn't request this, ignore this email.\n\n"
                           . "— SubTracker";

            $mail->send();
            $msg = "Reset link sent to your email. Check your inbox.";
        } catch (Exception $e) {
            $error = "Email failed: " . $mail->ErrorInfo;
        }
    } else {
        $error = 'No account found with that email.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">   
    <title>SubTracker — Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="floating-icons">
    <img src="icons/netflix.svg" class="float-img f1" alt="">
    <img src="icons/spotify.svg" class="float-img f2" alt="">
    <img src="icons/youtube.svg" class="float-img f3" alt="">
    <img src="icons/disneyplus.svg" class="float-img f4" alt="">
    <img src="icons/applemusic.svg" class="float-img f5" alt="">
    <img src="icons/hulu.svg" class="float-img f6" alt="">
    <img src="icons/adobe.svg" class="float-img f7" alt="">
    <img src="icons/openai.svg" class="float-img f8" alt="">
    <img src="icons/notion.svg" class="float-img f9" alt="">
    <img src="icons/icloud.svg" class="float-img f10" alt="">
    <img src="icons/hbomax.svg" class="float-img f11" alt="">
    <img src="icons/canva.svg" class="float-img f12" alt="">
</div>   
<div class="card">
    <h2>Forgot Password</h2>
    <p class="subtitle">Enter your email and we'll send you a reset link.</p>
    <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if($msg): ?><p class="success"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email address" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <p><a href="login.php">← Back to sign in</a></p>
</div>
<a href="help.php" class="help-float" title="Help & Support">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
</a>
<span class="help-float-text">Help</span>      
</body>
</html>   