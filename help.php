<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'auth.php';
$user = null;
if (is_logged_in()) {
    $user = get_user();
}   

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $issue = trim($_POST['issue'] ?? '');

    if (empty($email) || empty($issue)) {
        $error = 'Please fill in all fields.';
    } else {
        require_once 'phpmailer/PHPMailer.php';
        require_once 'phpmailer/SMTP.php';
        require_once 'phpmailer/Exception.php';

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->setFrom(MAIL_USERNAME, 'SubTracker Support');
            $mail->addAddress(MAIL_USERNAME);
            $mail->Subject = 'Support Request from ' . $email;
            $mail->Body    = "User: " . ($user ? $user['name'] : 'Not logged in') . "\n"
                           . "Account Email: " . $email . "\n"
                           . "Plan: " . ($user ? $user['plan'] : 'N/A') . "\n\n"   
                           . "Issue:\n" . $issue . "\n";
            $mail->send();
            $msg = 'Your request has been sent. We\'ll get back to you at ' . $email . ' as soon as possible.';
        } catch (Exception $e) {
            $error = 'Failed to send. Try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">   
    <title>SubTracker — Help & Support</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">
<nav>
    <span>SubTracker</span>
    <div class="nav-actions">
        <a href="index.php" class="nav-btn">← Back</a>
    </div>
</nav>
<div class="dash-container" style="max-width:700px;">
    <h2 style="color:#1e293b; margin-bottom:24px;">Help & Support</h2>

    <!-- App Download Banner -->
    <?php if ($user && $user['app_downloaded'] == 0): ?>
    <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6); border-radius:16px; padding:24px 28px; margin-bottom:20px; text-align:center;">
        <h3 style="color:#fff; font-size:18px; margin-bottom:8px;"> Get SubTracker on your phone</h3>
        <p style="color:rgba(255,255,255,0.8); font-size:13px; margin-bottom:16px;">Track your subscriptions on the go. Install the app for a full-screen experience with notifications.</p>
        <a href="app_download.php" style="display:inline-block; background:#fff; color:#1e293b; padding:14px 32px; border-radius:10px; font-size:15px; font-weight:700; text-decoration:none; transition:transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
             Download for Android
        </a>
        <p style="color:rgba(255,255,255,0.5); font-size:11px; margin-top:10px;">iPhone? Open SubTracker in Safari → Share → Add to Home Screen</p>
    </div>
    <?php endif; ?>   

    <!-- Pricing -->
    <div class="widget" style="margin-bottom:20px;">
        <h3> Pricing</h3>
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:12px;">
            <div style="flex:1; min-width:140px; background:#f0f4ff; border:2px solid #6366f1; border-radius:12px; padding:16px 20px; text-align:center;">
                <div style="font-size:24px; font-weight:800; color:#1e293b;">$5<span style="font-size:13px; font-weight:400; color:#64748b;">/month</span></div>
                <p style="font-size:12px; color:#64748b; margin-top:6px;">Billed monthly · Cancel anytime</p>
            </div>
            <div style="flex:1; min-width:140px; background:#f0fdf4; border:2px solid #22c55e; border-radius:12px; padding:16px 20px; text-align:center; position:relative;">
                <span style="position:absolute; top:-8px; right:12px; background:#22c55e; color:#fff; font-size:9px; font-weight:700; padding:2px 8px; border-radius:8px;">SAVE 33%</span>
                <div style="font-size:24px; font-weight:800; color:#1e293b;">$40<span style="font-size:13px; font-weight:400; color:#64748b;">/year</span></div>
                <p style="font-size:12px; color:#64748b; margin-top:6px;">Billed yearly · Cancel anytime</p>
            </div>
        </div>
        <p style="font-size:12px; color:#94a3b8; margin-top:12px;">7-day free trial included. No credit card required to start.</p>
    </div>

    <div class="widget" style="margin-bottom:20px;">
        <h3> How to use SubTracker</h3>
        <ul style="color:#475569; font-size:14px; line-height:2; padding-left:20px;">
            <li><strong>Add a subscription:</strong> Click "+ Add" in the top bar → search for your service → click "Add" → fill in price and renewal date</li>
            <li><strong>Cancel a subscription:</strong> Click the ✂️ icon next to any sub → opens that service's cancel page in a new tab</li>
            <li><strong>Edit a subscription:</strong> Click the ✏️ icon → change price, date, or category</li>
            <li><strong>Remove from tracker:</strong> Click the 🗑️ icon → removes it from your list (does NOT cancel the actual subscription)</li>
            <li><strong>View spending:</strong> The dashboard shows your monthly total, yearly cost, category breakdown, and fun stats</li>
            <li><strong>Renewal alerts:</strong> A yellow banner appears when a sub renews within 7 days</li>
            <li><strong>Notifications:</strong> Click the 🔔 bell icon on the dashboard → see all alerts and updates</li>
            <li><strong>Manage your SubTracker plan:</strong> Click " Manage" in the top bar → go to the upgrade page → choose $5/month or $40/year → click "Cancel Subscription" to manage or cancel on PayPal</li>
            <li><strong>Forgot password:</strong> Click "Forgot password?" on the login page → enter your email → check your inbox for a reset link</li>
        </ul>
    </div>

    <div class="widget">
        <h3> Contact Support</h3>
        <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if($msg): ?><p class="success"><?= htmlspecialchars($msg) ?></p><?php else: ?>
        <form method="POST">
            <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:6px;">Your account email</label>
            <input type="email" name="email" value="<?= $user ? htmlspecialchars($user['email']) : '' ?>" style="width:100%; padding:12px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; margin-bottom:14px;" required>
            <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:6px;">Describe your issue</label>
            <textarea name="issue" rows="5" placeholder="e.g. I want to delete my account and all my data" style="width:100%; padding:12px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; margin-bottom:14px; resize:vertical;" required></textarea>
            <button type="submit" style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; padding:12px 24px; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">Send</button>
        </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>   