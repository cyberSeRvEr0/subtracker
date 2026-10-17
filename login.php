<?php
require_once 'auth.php';
if (is_logged_in()) header('Location: index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        // Check if they need to see onboarding
        $stmt2 = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt2->execute([$user['id']]);
        $u = $stmt2->fetch();
        $needsOnboarding = false;
        if ($u['trial_used'] && $u['trial_end'] && strtotime($u['trial_end']) <= time() && $u['plan'] !== 'paid') {
            $needsOnboarding = true;
        }
        if ($u['plan'] === 'paid' && $u['sub_end'] && strtotime($u['sub_end']) <= time()) {
            $needsOnboarding = true;
        }
        if ($needsOnboarding) {
            header('Location: onboarding.php');
        } else {
            header('Location: index.php');
        }   
        exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">   
    <title>SubTracker — Sign In</title>
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
    <h2>Welcome back</h2>
    <p class="subtitle">Sign in to track your subscriptions.</p>
    <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email address" required>
        <div class="password-wrap">
            <input type="password" name="password" placeholder="Password" required id="loginPass">
            <button type="button" class="eye-btn" onclick="togglePass('loginPass', this)">👁</button>
        </div>   
        <button type="submit">Sign In</button>
    </form>
    <?php
    require_once 'config.php';
    $_SESSION['csrf_state'] = bin2hex(random_bytes(16));
    ?>

    <a href="https://accounts.google.com/o/oauth2/v2/auth?client_id=<?= urlencode(GOOGLE_CLIENT_ID) ?>&redirect_uri=<?= urlencode(APP_URL . '/google_callback.php') ?>&response_type=code&scope=<?= urlencode('openid email profile') ?>&access_type=offline&prompt=select_account&state=<?= urlencode($_SESSION['csrf_state']) ?>"
       class="google-btn" style="text-decoration:none; display:flex;">
      <svg class="google-icon" viewBox="0 0 48 48" width="24" height="24"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
      Sign in with Google
    </a>               
    <p><a href="forgot_password.php">Forgot password?</a></p>
    <p>No account? <a href="register.php">Create one</a></p>
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