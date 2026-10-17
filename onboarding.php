<?php
ob_start();
require_once 'auth.php';
require_login();   

$user = get_user();

// If they're already paid, skip this page
if ($user['plan'] === 'paid' && $user['sub_end'] && strtotime($user['sub_end']) > time()) {
    header('Location: ' . APP_URL . '/index.php');   
    exit;
}

// If trial is active, skip
if ($user['trial_end'] && strtotime($user['trial_end']) > time() && $user['plan'] !== 'free') {
    header('Location: index.php');
    exit;
}

// Determine state
$trialActive = $user['trial_end'] && strtotime($user['trial_end']) > time();
$trialEnded = $user['trial_used'] && (!$user['trial_end'] || strtotime($user['trial_end']) <= time());
$subActive = $user['sub_end'] && strtotime($user['sub_end']) > time();
$subExpired = $user['plan'] === 'paid' && $user['sub_end'] && strtotime($user['sub_end']) <= time();

// Handle "Start Free Trial" click
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'start_trial' && !$user['trial_used']) {
        $now = date('Y-m-d H:i:s');
        $end = date('Y-m-d H:i:s', strtotime('+7 days'));
        $stmt = $pdo->prepare("UPDATE users SET trial_start=?, trial_end=?, trial_used=1, plan='trial' WHERE id=?");
        $stmt->execute([$now, $end, $_SESSION['user_id']]);
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}
?>   

<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>   
    <link rel="icon" type="image/svg+xml" href="favicon.svg">   
    <title>SubTracker — Get Started</title>
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

<div class="onboarding-card">
    <h2>Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>

    <?php if ($trialEnded || $subExpired): ?>
        <p class="onboarding-msg trial-ended">⚠️ Your free trial has ended. Upgrade now to keep managing your subscriptions.</p>
    <?php elseif ($user['trial_used'] && !$trialActive): ?>
        <p class="onboarding-msg trial-ended">⚠️ Your trial period is over. Upgrade to continue.</p>
    <?php else: ?>
        <p class="onboarding-msg">Choose how you want to get started:</p>
    <?php endif; ?>

    <div class="onboarding-options">
        <?php if (!$user['trial_used'] && !$trialEnded && !$subExpired): ?>
        <form method="POST" class="option-card trial-card">
            <div class="option-icon">🎁</div>
            <h3>7-Day Free Trial</h3>
            <p>Full access for 7 days. No card required.</p>
            <input type="hidden" name="action" value="start_trial">
            <button type="submit">Start Free Trial</button>
        </form>
        <?php endif; ?>

        <a href="upgrade.php" class="option-card upgrade-card">
            <div class="option-icon">⚡</div>
            <h3>Upgrade Now</h3>
            <p>$5/month or $40/year · Unlimited subscriptions · Cancel anytime</p>   
            <span class="upgrade-btn">Upgrade Now →</span>
        </a>
    </div>

    <?php if ($trialEnded || $subExpired): ?>
    <p class="onboarding-footer">Your saved subscriptions are safe. They'll be waiting when you upgrade.</p>
    <?php endif; ?>
</div>
<a href="help.php" class="help-float" title="Help & Support">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
</a>
<span class="help-float-text">Help</span>      
</body>
</html>   