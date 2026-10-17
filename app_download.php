<?php
require_once 'config.php';
require_once 'auth.php';
require_login();

$stmt = $pdo->prepare("UPDATE users SET app_downloaded = 1 WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);

header('Content-Type: application/vnd.android.package-archive');
header('Content-Disposition: attachment; filename="subtracker.apk"');
header('Content-Length: ' . filesize(__DIR__ . '/download/subtracker.apk'));
readfile(__DIR__ . '/download/subtracker.apk');
exit;
?>   