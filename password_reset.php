<?php
require_once 'auth.php';
if (is_logged_in()) header('Location: index.php');
$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid link.");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Invalid or expired reset link.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (strlen($pass) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($pass !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        $success = 'Password updated! You can now <a href="login.php">sign in</a>.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>   
    <link rel="icon" type="image/svg+xml" href="favicon.svg">   
    <title>SubTracker — Reset Password</title>
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
    <h2>Reset Password</h2>
    <p class="subtitle">Choose a new password for your account.</p>
    <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if($success): ?><p class="success"><?= $success ?></p><?php else: ?>
    <form method="POST">
        <div class="password-wrap">
            <input type="password" name="password" placeholder="Password" required id="loginPass">
            <button type="button" class="eye-btn" onclick="togglePass('loginPass', this)">👁</button>
        </div>   
        <div class="password-wrap">
            <input type="password" name="confirm" placeholder="Confirm new password" required id="confPass">
            <button type="button" class="eye-btn" onclick="togglePass('confPass', this)">👁</button>
        </div>
        <button type="submit">Update Password</button>
    </form>
    <?php endif; ?>
</div>
<script>
function togglePass(id, btn) {
    const inp = document.getElementById(id);
    if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '👁‍🗨'; }
    else { inp.type = 'password'; btn.textContent = '👁'; }
}
</script> 
<a href="help.php" class="help-float" title="Help & Support">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
</a>
<span class="help-float-text">Help</span>      
</body>
</html>   