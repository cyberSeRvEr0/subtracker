<?php
require_once 'auth.php';
require_login();

$stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$sub = $stmt->fetch();
if (!$sub) die("Not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE subscriptions SET name=?, price=?, renewal_date=?, category=? WHERE id=? AND user_id=?");
    $stmt->execute([trim($_POST['name']), floatval($_POST['price']), $_POST['renewal_date'], trim($_POST['category'] ?? 'other'), $sub['id'], $_SESSION['user_id']]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
    <title>SubTracker — Edit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">
<nav>
    <span>SubTracker</span>
    <div class="nav-actions">
        <a href="index.php" class="nav-btn">← Back</a>
    </div>
</nav>

<div class="dash-container">
    <div class="add-form-card">
        <h2>Edit: <?= htmlspecialchars($sub['name']) ?></h2>
        <form method="POST">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($sub['name']) ?>" required>
            <label>Monthly Price ($)</label>
            <input type="number" name="price" step="0.01" min="0" value="<?= $sub['price'] ?>" required>
            <label>Renewal Date</label>
            <input type="date" name="renewal_date" value="<?= $sub['renewal_date'] ?>" required>
            <label>Category</label>
            <select name="category">
                <option value="streaming" <?= $sub['category']==='streaming'?'selected':'' ?>>Streaming</option>
                <option value="music" <?= $sub['category']==='music'?'selected':'' ?>>Music</option>
                <option value="fitness" <?= $sub['category']==='fitness'?'selected':'' ?>>Fitness</option>
                <option value="cloud" <?= $sub['category']==='cloud'?'selected':'' ?>>Cloud / Storage</option>
                <option value="other" <?= $sub['category']==='other'?'selected':'' ?>>Other</option>
            </select>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>
</body>
</html>   