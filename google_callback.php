<?php
require_once 'config.php';
session_start();

$CLIENT_ID = GOOGLE_CLIENT_ID;
$CLIENT_SECRET = GOOGLE_CLIENT_SECRET;
$REDIRECT_URI = APP_URL . '/google_callback.php';   

// Verify state
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['csrf_state']) {
    die("Invalid state");
}
unset($_SESSION['csrf_state']);

if (isset($_GET['error'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['code'])) {
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

$tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'code'          => $code,
            'client_id'     => $CLIENT_ID,
            'client_secret' => $CLIENT_SECRET,
            'redirect_uri'  => $REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]),
    ],
]));

$tokenData = json_decode($tokenResponse, true);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    header('Location: login.php');
    exit;
}

$userInfoResponse = file_get_contents('https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . urlencode($accessToken));
$googleUser = json_decode($userInfoResponse, true);

if (!isset($googleUser['email'])) {
    header('Location: login.php');
    exit;
}

$email = $googleUser['email'];
$name = $googleUser['name'] ?? 'User';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];

    $needsOnboarding = false;
    if ($user['trial_used'] && $user['trial_end'] && strtotime($user['trial_end']) <= time() && $user['plan'] !== 'paid') {
        $needsOnboarding = true;
    }
    if ($user['plan'] === 'paid' && $user['sub_end'] && strtotime($user['sub_end']) <= time()) {
        $needsOnboarding = true;
    }

    if ($needsOnboarding) {
        header('Location: onboarding.php');
    } else {
        header('Location: index.php');
    }
    exit;
} else {
    $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hash]);

    session_regenerate_id(true);
    $_SESSION['user_id'] = $pdo->lastInsertId();
    header('Location: onboarding.php');
    exit;
}     