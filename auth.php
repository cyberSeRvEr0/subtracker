<?php
require_once 'config.php';
function is_logged_in() { return isset($_SESSION['user_id']); }
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
    // Check if user still exists
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}   
function get_user() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    return $user;
}   
function count_subs($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}
