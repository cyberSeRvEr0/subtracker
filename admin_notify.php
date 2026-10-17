<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'settings.php';
require_once 'db.php';
require_once 'phpmailer/PHPMailer.php';
require_once 'phpmailer/SMTP.php';
require_once 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$ADMIN_PASSWORD = 'ptracesigsegv';

// Handle delete from list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notif'])) {
    if (isset($_POST['admin_pass']) && $_POST['admin_pass'] === $ADMIN_PASSWORD) {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->execute([(int)$_POST['delete_notif']]);
    }
    header('Location: admin_notify.php?view=1');
    exit;
}

$error = '';
$success = '';
$allNotifications = [];
$allNotifications = $pdo->query("SELECT n.*, u.name as user_name, u.email FROM notifications n JOIN users u ON n.user_id = u.id ORDER BY n.created_at DESC LIMIT 50")->fetchAll();   
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['admin_pass']) || $_POST['admin_pass'] !== $ADMIN_PASSWORD) {
        $error = 'Wrong password.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $target = $_POST['target'] ?? 'specific';
            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $type = $_POST['type'] ?? 'info';
            $send_email = isset($_POST['send_email']);

            if (!$title || !$message) {
                $error = 'Fill in all fields.';
            } else {
                $sentCount = 0;

                if ($target === 'all') {
                    $users = $pdo->query("SELECT id, email, name FROM users")->fetchAll();
                    foreach ($users as $u) {
                        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$u['id'], $title, $message, $type]);
                        $sentCount++;

                        if ($send_email && $u['email']) {
                            try {
                                $mail = new PHPMailer(true);
                                $mail->isSMTP();
                                $mail->Host       = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = MAIL_USERNAME;
                                $mail->Password   = MAIL_PASSWORD;
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                $mail->Port       = 465;
                                $mail->setFrom(MAIL_USERNAME, 'SubTracker');
                                $mail->addAddress($u['email']);
                                $mail->Subject = "You've got a new notification in SubTracker";
                                $mail->Body    = "Hi " . $u['name'] . ",\n\n" . $title . "\n\n" . $message . "\n\n — SubTracker";
                                $mail->send();
                            } catch (Exception $e) {
                                error_log("Email to {$u['email']} failed: " . $e->getMessage());
                            }
                        }
                    }
                    header('Location: admin_notify.php?sent=all&count=' . $sentCount);
                    exit;
                } else {
                    $user_id = (int)($_POST['user_id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();

                    if (!$user) {
                        $error = 'User #' . $user_id . ' not found.';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$user_id, $title, $message, $type]);

                        if ($send_email) {
                            try {
                                $mail = new PHPMailer(true);
                                $mail->isSMTP();
                                $mail->Host       = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = MAIL_USERNAME;
                                $mail->Password   = MAIL_PASSWORD;
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                $mail->Port       = 465;
                                $mail->setFrom(MAIL_USERNAME, 'SubTracker');
                                $mail->addAddress($user['email']);
                                $mail->Subject = "You've got a new notification in SubTracker";
                                $mail->Body    = "Hi " . $user['name'] . ",\n\n" . $title . "\n\n" . $message . "\n\n— SubTracker";
                                $mail->send();
                                header('Location: admin_notify.php?sent=1&name=' . urlencode($user['name']));
                                exit;
                            } catch (Exception $e) {
                                header('Location: admin_notify.php?sent=1&name=' . urlencode($user['name']) . '&emailerr=1');
                                exit;
                            }
                        } else {
                            header('Location: admin_notify.php?sent=1&name=' . urlencode($user['name']));
                            exit;
                        }
                    }
                }
            }
		}
    }
}

// Show success message from redirect
if (isset($_GET['sent'])) {
    if ($_GET['sent'] === 'all') {
        $success = "Notification sent to all " . (int)$_GET['count'] . " users (emails sent).";
    } else {
        $name = urldecode($_GET['name'] ?? 'user');
        $success = 'Notification sent to ' . $name . '.';
        if (isset($_GET['emailerr'])) $success .= ' Email failed.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Admin — Notifications</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { overflow-y: auto !important; }
        .admin-container {
            max-width: 1100px;
            width: 100%;
            z-index: 1;
            padding: 40px 20px;
        }   
        .admin-container h2 { color: #fff; font-size: 24px; margin-bottom: 24px; }
        .admin-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }
        .admin-left {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .admin-right {
            position: sticky;
            top: 20px;
        }
        @media (max-width: 768px) {
            .admin-columns { grid-template-columns: 1fr; }
        }   
        .admin-widget {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .admin-widget h3 { color: #1e293b; font-size: 15px; margin-bottom: 14px; font-weight: 700; }
        .admin-widget label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 4px;
            margin-top: 10px;
        }
        .admin-widget input[type="text"],
        .admin-widget input[type="number"],
        .admin-widget input[type="password"],
        .admin-widget textarea,
        .admin-widget select {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            color: #1e293b;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .admin-widget input:focus,
        .admin-widget textarea:focus,
        .admin-widget select:focus {
            outline: none;
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        .admin-widget select option { background: #fff; color: #1e293b; }
        .admin-widget input::placeholder,
        .admin-widget textarea::placeholder { color: #94a3b8; }
        .admin-widget textarea { resize: vertical; }
        .admin-btn {
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .admin-btn:hover { transform: translateY(-2px); }
        .admin-btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; }
        .admin-btn-primary:hover { box-shadow: 0 8px 25px rgba(99,102,241,0.4); }
        .admin-btn-secondary { background: #64748b; color: #fff; }
        .admin-btn-secondary:hover { background: #475569; }
        .admin-check {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #475569;
            margin-top: 12px;
            cursor: pointer;
        }
        .admin-table {
            width: 100%;
            font-size: 13px;
            border-collapse: collapse;
        }
        .admin-table th {
            color: #94a3b8;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #e2e8f0;
            font-size: 11px;
            text-transform: uppercase;
        }
        .admin-table td {
            color: #334155;
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .admin-table .del-link {
            color: #94a3b8;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            background: none;
            border: none;
            padding: 2px 6px;
            border-radius: 4px;
            transition: color 0.2s, background 0.2s;
        }
        .admin-table .del-link:hover {
            color: #ef4444;
            background: #fef2f2;
        }
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
<div class="admin-container">
    <h2>⚙️ Admin — Notifications</h2>

    <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <div class="admin-columns">
        <!-- LEFT: Forms -->
        <div class="admin-left">
            <div class="admin-widget">
                <h3>📩 New Notification</h3>
                <form method="POST">
                    <label>Admin Password</label>
                    <input type="password" name="admin_pass" placeholder="Admin password" required>
                    <label>Send to</label>
                    <select name="target" id="targetSelect">
                        <option value="specific">Specific user</option>
                        <option value="all">All users</option>
                    </select>
                    <div id="userIdField">
                        <label>User ID</label>
                        <input type="number" name="user_id" placeholder="e.g. 1">
                    </div>
                    <label>Title</label>
                    <input type="text" name="title" placeholder="e.g. Netflix price increase" required>
                    <label>Message</label>
                    <textarea name="message" rows="4" placeholder="e.g. Netflix just raised their Standard plan from $15.49 to $17.99." required></textarea>
                    <label>Type</label>
                    <select name="type">
                        <option value="info">Info</option>
                        <option value="price">Price Change</option>
                        <option value="renewal">Renewal Reminder</option>
                        <option value="update">Update</option>
                        <option value="alert">Alert</option>
                    </select>
                    <label class="admin-check">
                        <input type="checkbox" name="send_email" checked> Also send email to user(s)
                    </label>
                    <br><br>
                    <button type="submit" name="action" value="add" class="admin-btn admin-btn-primary">Send Notification</button>
                </form>
            </div>
        </div>

        <!-- RIGHT: List -->
        <div class="admin-right">
            <?php if (!empty($allNotifications)): ?>
            <div class="admin-widget" style="max-height:600px; overflow-y:auto;">
                <h3>📋 Recent (last 50)</h3>
                <table class="admin-table">
                    <thead><tr><th>User</th><th>Title</th><th>Type</th><th>Read</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($allNotifications as $n): ?>
                        <tr>
                            <td><?= htmlspecialchars($n['user_name']) ?> (#<?= $n['user_id'] ?>)</td>
                            <td><?= htmlspecialchars($n['title']) ?></td>
                            <td><?= $n['type'] ?></td>
                            <td><?= $n['read'] ? '✅' : '🔴' ?></td>
                            <td><?= $n['created_at'] ?></td>
                            <td>
                                <form method="POST" style="display:inline; margin:0;">
                                    <input type="hidden" name="admin_pass" value="">
                                    <input type="hidden" name="delete_notif" value="<?= $n['id'] ?>">
                                    <button type="submit" class="del-link" onclick="this.form.admin_pass.value='<?= $ADMIN_PASSWORD ?>'">delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="admin-widget" style="text-align:center; padding:40px;">
                <p style="color:#94a3b8; font-size:13px;">No notifications yet.<br>Send one from the left panel.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('targetSelect').addEventListener('change', function() {
    document.getElementById('userIdField').style.display = this.value === 'specific' ? 'block' : 'none';
});
</script>
</body>   
</html>   