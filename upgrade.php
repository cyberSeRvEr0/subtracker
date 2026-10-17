<?php
require_once 'auth.php';
require_login();
$user = get_user();

$client_id = PAYPAL_CLIENT_ID;
$secret = PAYPAL_CLIENT_SECRET;
$monthly_plan_id = PAYPAL_PLAN_ID_MONTHLY;
$annual_plan_id = PAYPAL_PLAN_ID_ANNUAL;
$env = PAYPAL_ENV ?: 'sandbox';
$paypalBase = $env === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

// Handle return from PayPal
if (isset($_GET['subscription_id'])) {
    $now = date('Y-m-d H:i:s');
    $billing = $_GET['billing'] ?? 'monthly';
    $end = $billing === 'annual'
        ? date('Y-m-d H:i:s', strtotime('+1 year'))
        : date('Y-m-d H:i:s', strtotime('+1 month'));
    $sub_id = $_GET['subscription_id'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();

    if ($u['plan'] === 'paid' && $u['sub_end'] && strtotime($u['sub_end']) > time()) {
        $stmt = $pdo->prepare("UPDATE users SET pending_upgrade = 1, paypal_sub_id = ? WHERE id = ?");
        $stmt->execute([$sub_id, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET plan = 'paid', sub_start = ?, sub_end = ?, pending_upgrade = 0, paypal_sub_id = ? WHERE id = ?");
        $stmt->execute([$now, $end, $sub_id, $_SESSION['user_id']]);
    }

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>   
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>SubTracker — Upgrade</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://www.paypal.com/sdk/js?client-id=<?= $client_id ?>&vault=true&intent=subscription"></script>
</head>
<body class="dashboard-body">
<nav>
    <span>SubTracker</span>
    <div class="nav-actions">
        <a href="onboarding.php" class="nav-btn">← Back</a>
    </div>
</nav>
<div class="dash-container">
    <div class="add-form-card" style="text-align:center;">
        <h2>Upgrade to Premium</h2>
        <p style="color:#64748b; margin-bottom:20px;">Unlimited subscriptions · Cancel anytime</p>

        <!-- Billing toggle -->
        <div style="display:flex; gap:0; justify-content:center; margin-bottom:20px; border-radius:10px; overflow:hidden; border:2px solid #e2e8f0;">
            <button id="btnMonthly" onclick="selectPlan('monthly')" style="padding:12px 24px; border:none; cursor:pointer; font-size:14px; font-weight:600; background:#6366f1; color:#fff; transition:all 0.2s;">
                $5<span style="font-size:11px; font-weight:400;">/month</span>
            </button>
            <button id="btnAnnual" onclick="selectPlan('annual')" style="padding:12px 24px; border:none; cursor:pointer; font-size:14px; font-weight:600; background:#e2e8f0; color:#475569; transition:all 0.2s; position:relative;">   
                $40<span style="font-size:11px; font-weight:400;">/year</span>
                <span style="position:absolute; top:-8px; right:-4px; background:#22c55e; color:#fff; font-size:9px; font-weight:700; padding:2px 6px; border-radius:8px;">SAVE 33%</span>
            </button>
        </div>

        <div id="paypal-button-container"></div>

        <?php if ($user['plan'] === 'paid' && !empty($user['sub_end']) && strtotime($user['sub_end']) > time()): ?>
        <p style="margin-top:16px; font-size:12px; color:#94a3b8;">Your subscription is active. It renews automatically.</p>
        <a href="https://www.paypal.com/myaccount/autopay" target="_blank" style="display:inline-block; margin-top:12px; background:#fff; color:#333; border:2px solid #e2e8f0; padding:10px 20px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none;">✂️ Cancel Subscription</a>
        <?php else: ?>
        <p style="margin-top:16px; font-size:12px; color:#94a3b8;">You'll be redirected to PayPal to approve the payment.</p>
        <?php endif; ?>
    </div>

    <?php
    $showClock = false;
    $clockRemaining = 0;
    $clockLabel = '';

    if ($user['plan'] === 'trial' && !empty($user['trial_end']) && strtotime($user['trial_end']) > time()) {
        $clockRemaining = strtotime($user['trial_end']) - time();
        $showClock = true;
        $clockLabel = 'Free trial ends in';
    } elseif ($user['plan'] === 'paid' && !empty($user['sub_end']) && strtotime($user['sub_end']) > time()) {
        $clockRemaining = strtotime($user['sub_end']) - time();
        $showClock = true;
        $clockLabel = 'Subscription renews in';
    }

    if ($showClock):
        $remaining = $clockRemaining;
        $daysLeft = floor($remaining / 86400);
        $hoursLeft = floor(($remaining % 86400) / 3600);
        $minsLeft = floor(($remaining % 3600) / 60);
        $secsLeft = $remaining % 60;
        $isPaid = ($user['plan'] === 'paid');
    ?>
    <div class="clock-countdown" id="clockCountdown">
        <div class="clock-days">
            <span class="clock-days-num" id="clockDays"><?= $daysLeft ?></span>
            <span class="clock-days-label">days remaining</span>
        </div>
        <div class="clock-face">
            <div class="clock-tick" style="transform: rotate(0deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(30deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(60deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(90deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(120deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(150deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(180deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(210deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(240deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(270deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(300deg) translate(-50%, 0); top: 4px;"></div>
            <div class="clock-tick" style="transform: rotate(330deg) translate(-50%, 0); top: 4px;"></div>
            <?php if ($isPaid): ?>
            <div class="clock-hand hour-hand" id="clockHour" style="transform: rotate(<?= date('G') * 30 + date('i') * 0.5 ?>deg);"></div>
            <div class="clock-hand minute-hand" id="clockMin" style="transform: rotate(<?= date('i') * 6 + date('s') * 0.1 ?>deg);"></div>
            <div class="clock-hand second-hand" id="clockSec" style="transform: rotate(<?= date('s') * 6 ?>deg);"></div>
            <?php else: ?>
            <div class="clock-hand hour-hand" id="clockHour" style="transform: rotate(<?= $hoursLeft * 30 + $minsLeft * 0.5 ?>deg);"></div>
            <div class="clock-hand minute-hand" id="clockMin" style="transform: rotate(<?= $minsLeft * 6 + $secsLeft * 0.1 ?>deg);"></div>
            <div class="clock-hand second-hand" id="clockSec" style="transform: rotate(<?= $secsLeft * 6 ?>deg);"></div>
            <?php endif; ?>
            <div class="clock-center"></div>
        </div>
        <div class="clock-digital" id="clockDigital"><?= sprintf('%02d:%02d:%02d', $hoursLeft, $minsLeft, $secsLeft) ?></div>
        <div class="clock-label"><?= $clockLabel ?></div>
    </div>
    <script>
    const isPaid = <?= $isPaid ? 'true' : 'false' ?>;
    const target = <?= (int)(strtotime($user['plan'] === 'paid' ? $user['sub_end'] : $user['trial_end']) * 1000) ?>;
    const daysEl = document.getElementById('clockDays');
    const hourEl = document.getElementById('clockHour');
    const minEl = document.getElementById('clockMin');
    const secEl = document.getElementById('clockSec');
    const digitalEl = document.getElementById('clockDigital');
    const box = document.getElementById('clockCountdown');

    const iv = setInterval(() => {
        const remaining = Math.floor((target - Date.now()) / 1000);
        if (remaining <= 0) { clearInterval(iv); box.style.display = 'none'; return; }
        const d = Math.floor(remaining / 86400);
        const h = Math.floor((remaining % 86400) / 3600);
        const m = Math.floor((remaining % 3600) / 60);
        const s = remaining % 60;
        daysEl.textContent = d;
        if (isPaid) {
            const now = new Date();
            const ch = now.getHours();
            const cm = now.getMinutes();
            const cs = now.getSeconds();
            hourEl.style.transform = 'rotate(' + (ch * 30 + cm * 0.5) + 'deg)';
            minEl.style.transform = 'rotate(' + (cm * 6 + cs * 0.1) + 'deg)';
            secEl.style.transform = 'rotate(' + (cs * 6) + 'deg)';
        } else {
            hourEl.style.transform = 'rotate(' + (h * 30 + m * 0.5) + 'deg)';
            minEl.style.transform = 'rotate(' + (m * 6 + s * 0.1) + 'deg)';
            secEl.style.transform = 'rotate(' + (s * 6) + 'deg)';
        }
        digitalEl.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    }, 1000);
    </script>
    <?php endif; ?>
</div>

<script>
let selectedPlan = 'monthly';
const monthlyId = '<?= $monthly_plan_id ?>';
const annualId = '<?= $annual_plan_id ?>';

function selectPlan(plan) {
    selectedPlan = plan;
    const btnM = document.getElementById('btnMonthly');
    const btnA = document.getElementById('btnAnnual');
    if (plan === 'monthly') {
        btnM.style.background = '#6366f1';
        btnM.style.color = '#fff';
        btnA.style.background = '#fff';
        btnA.style.color = '#1e293b';
    } else {
        btnA.style.background = '#22c55e';
        btnA.style.color = '#fff';
        btnM.style.background = '#fff';
        btnM.style.color = '#1e293b';
    }
    renderPayPal();
}

function renderPayPal() {
    const container = document.getElementById('paypal-button-container');
    container.innerHTML = '';
    paypal.Buttons({
        style: {
            shape: 'rect',
            color: 'gold',
            layout: 'vertical',
            label: 'paypal'
        },
        createSubscription: function(data, actions) {
            return actions.subscription.create({
                plan_id: selectedPlan === 'annual' ? annualId : monthlyId
            });
        },
        onApprove: function(data, actions) {
            window.location.href = "<?= APP_URL ?>/upgrade.php?subscription_id=" + data.subscriptionID + "&billing=" + selectedPlan;   
        },
        onError: function(err) {
            alert("PayPal error: " + JSON.stringify(err));
        }
    }).render('#paypal-button-container');
}

renderPayPal();
</script>
<a href="help.php" class="help-float" title="Help & Support">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
</a>
<span class="help-float-text">Help</span>
</body>
</html>   