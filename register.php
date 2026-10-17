<?php
require_once 'auth.php';
if (is_logged_in()) header('Location: onboarding.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            session_regenerate_id(true);
            header('Location: onboarding.php');
            exit;
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
    <title>SubTracker — Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="left-align">   
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
<div class="right-panel">
    <div class="typewriter-box">
        <span class="typewriter" id="typeText"></span><span class="cursor">|</span>
    </div>
<div class="carousel-container">
    <div class="carousel-ring">
        <div class="carousel-card">
            <div class="mini-graph">
                <div class="bar bar1"></div>
                <div class="bar bar2"></div>
                <div class="bar bar3"></div>
                <div class="bar bar4"></div>
            </div>
            <p class="card-text red">Track spending at a glance</p>
        </div>
        <div class="carousel-card">
            <div class="mini-counter">
                <span class="counter-label">Saved this year</span>
                <span class="counter-value" id="saveCounter">$0</span>
            </div>
            <p class="card-text green">Cancel unused subs & save</p>
        </div>
        <div class="carousel-card">
            <div class="mini-donut">
                <div class="donut-hole"></div>
            </div>
            <p class="card-text purple">See where money goes</p>
        </div>
        <div class="carousel-card">
            <div class="mini-alert">
                <span class="alert-dot"></span>
                <span class="alert-text">Netflix renews in 3 days</span>
            </div>
            <p class="card-text amber">Never get surprised again</p>
        </div>
        <div class="carousel-card">
            <video src="video/banner.mp4" autoplay loop muted playsinline style="width:100%; height:80px; object-fit:cover; border-radius:8px;"></video>
            <p class="card-text red">Track spending at a glance</p>
        </div>   
    </div>
</div>
</div>
<div class="card">   
    <h2>SubTracker</h2>
    <p class="subtitle">Track every subscription. Stop overpaying.</p>
    <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Your name" required>
        <input type="email" name="email" placeholder="Email address" required>
        <div class="password-wrap">
            <input type="password" name="password" placeholder="Password" required id="loginPass">
            <button type="button" class="eye-btn" onclick="togglePass('loginPass', this)">👁</button>
        </div>     
        <button type="submit">Create Account</button>
    </form>
    <p>Already have an account? <a href="login.php">Sign in</a></p>
</div>
<script>
function togglePass(id, btn) {
    const inp = document.getElementById(id);
    if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '👁‍🗨'; }
    else { inp.type = 'password'; btn.textContent = '👁'; }
}

const phrases = [
    "Stop forgetting what you pay for every month.",
    "Add your subs. See the total. Cancel what you don't need.",
    "One dashboard. Every subscription. Zero surprises.",
    "Save an average of $120/year by spotting unused subs."
];
let pIdx = 0, cIdx = 0, deleting = false;
function type() {
    const el = document.getElementById('typeText');
    const current = phrases[pIdx];
    if (!deleting) {
        el.textContent = current.substring(0, ++cIdx);
        if (cIdx === current.length) { deleting = true; setTimeout(type, 2000); return; }
    } else {
        el.textContent = current.substring(0, --cIdx);
        if (cIdx === 0) { deleting = false; pIdx = (pIdx + 1) % phrases.length; }
    }
    setTimeout(type, deleting ? 30 : 60);
}
type();

// Counter animation
let count = 0;
const target = 1240;
const counterEl = document.getElementById('saveCounter');
function tick() {
    count += 12;
    if (count >= target) { count = 0; }
    counterEl.textContent = '$' + count.toLocaleString();
    setTimeout(tick, 50);
}
tick();
</script> 
<a href="help.php" class="help-float" title="Help & Support">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
</a>
<span class="help-float-text">Help</span>    
</body>     