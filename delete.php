<?php
require_once 'auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT name FROM subscriptions WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $_SESSION['user_id']]);
    $sub = $stmt->fetch();
    $name = $sub['name'] ?? 'Subscription';

    $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $_SESSION['user_id']]);

    header("Location: index.php?deleted=" . urlencode($name));
    exit;
}
header('Location: index.php');   