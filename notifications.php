<?php
require_once 'config.php';
require_once 'auth.php';
require_login();

$user = get_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_POST['delete_id'], $_SESSION['user_id']]);
    header('Location: notifications.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE notifications SET read = 1 WHERE user_id = ? AND read = 0");
$stmt->execute([$_SESSION['user_id']]);

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script> 
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>SubTracker — Notifications</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { overflow-y: auto !important; }   
        .notif-container {
            max-width: 600px;
            width: 100%;
            z-index: 1;
            padding: 40px 20px;
        }
        .notif-container h2 { color: #fff; font-size: 24px; margin-bottom: 24px; }
        .notif-back { margin-bottom: 20px; }
        .notif-back a { color: #818cf8; text-decoration: none; font-size: 14px; }
        .notif-back a:hover { text-decoration: underline; }
        .notif-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            display: flex;
            align-items: flex-start;
            gap: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .notif-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .notif-card.type-price { border-left: 4px solid #ef4444; }
        .notif-card.type-renewal { border-left: 4px solid #f59e0b; }
        .notif-card.type-update { border-left: 4px solid #3b82f6; }
        .notif-card.type-alert { border-left: 4px solid #8b5cf6; }
        .notif-card.type-info { border-left: 4px solid #6366f1; }
        .notif-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .type-info .notif-icon { background: #eef2ff; }
        .type-price .notif-icon { background: #fef2f2; }
        .type-renewal .notif-icon { background: #fffbeb; }
        .type-update .notif-icon { background: #eff6ff; }
        .type-alert .notif-icon { background: #f5f3ff; }
        .notif-body { flex: 1; }
        .notif-title { font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .notif-msg { font-size: 13px; color: #475569; line-height: 1.5; }
        .notif-date { font-size: 11px; color: #94a3b8; white-space: nowrap; margin-top: 6px; }
        .notif-delete {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            opacity: 0.5;
            transition: opacity 0.2s, transform 0.2s;
            padding: 4px;
            flex-shrink: 0;
        }   
        .notif-card:hover .notif-delete { opacity: 1; }
        .notif-delete:hover { transform: scale(1.2); }
        .notif-type-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2px 8px;
            border-radius: 10px;
            margin-bottom: 6px;
        }
        .type-info .notif-type-badge { background: #eef2ff; color: #6366f1; }
        .type-price .notif-type-badge { background: #fef2f2; color: #ef4444; }
        .type-renewal .notif-type-badge { background: #fffbeb; color: #d97706; }
        .type-update .notif-type-badge { background: #eff6ff; color: #3b82f6; }
        .type-alert .notif-type-badge { background: #f5f3ff; color: #8b5cf6; }
    </style>
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
<div class="notif-container">
    <div class="notif-back"><a href="index.php">← Back to Dashboard</a></div>
    <h2>🔔 Notifications</h2>

    <?php if (empty($notifications)): ?>
    <div style="text-align:center; padding:60px 20px;">
        <p style="font-size:48px; margin-bottom:16px;">📭</p>
        <p style="color:rgba(255,255,255,0.5); font-size:15px; font-weight:600;">No notifications yet</p>
        <p style="color:rgba(255,255,255,0.3); font-size:13px; margin-top:8px;">You'll see alerts here when something happens.</p>
    </div>
    <?php else: ?>
    <?php
    $typeIcons = ['info' => 'ℹ️', 'price' => '💰', 'renewal' => '🔄', 'update' => '📦', 'alert' => '⚠️'];
    $typeLabels = ['info' => 'Info', 'price' => 'Price Change', 'renewal' => 'Renewal', 'update' => 'Update', 'alert' => 'Alert'];
    foreach ($notifications as $n):
        $type = $n['type'] ?? 'info';
        $icon = $typeIcons[$type] ?? 'ℹ️';
        $label = $typeLabels[$type] ?? 'Info';
    ?>
    <div class="notif-card type-<?= $type ?>">
        <div class="notif-icon"><?= $icon ?></div>
        <div class="notif-body">
            <span class="notif-type-badge"><?= $label ?></span>
            <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
            <p class="notif-msg"><?= htmlspecialchars($n['message']) ?></p>
            <div class="notif-date"><?= $n['created_at'] ?></div>
        </div>
        <form method="POST" style="margin:0;">
            <input type="hidden" name="delete_id" value="<?= $n['id'] ?>">
            <button type="submit" class="notif-delete" title="Delete">🗑️</button>
        </form>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<a href="help.php" class="help-float" title="Help & Support">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
</a>
<span class="help-float-text">Help</span>
</body>
</html>   