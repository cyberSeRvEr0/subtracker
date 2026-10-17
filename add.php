<?php
require_once 'auth.php';
require_login();

$preName = $_GET['service'] ?? '';
$preCat = $_GET['cat'] ?? 'other';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $date = $_POST['renewal_date'];
    $category = trim($_POST['category'] ?? 'other');
    $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, name, price, renewal_date, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $name, $price, $date, $category]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
    <title>SubTracker — Add</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">
<nav>
    <span>SubTracker</span>
    <div class="nav-actions">
        <a href="services.php" class="nav-btn">← Back to services</a>
    </div>
</nav>

<div class="dash-container">
    <div class="add-form-card">
        <h2>Add Subscription</h2>
        <form method="POST">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($preName) ?>" required>
            <label>Monthly Price ($)</label>
            <input type="number" name="price" step="0.01" min="0" placeholder="e.g. 15.49" required>
            <label>Renewal Date</label>
            <input type="date" name="renewal_date" required>
            <label>Category</label>
            <select name="category">
                <option value="streaming" <?= $preCat==='streaming'?'selected':'' ?>>Streaming</option>
                <option value="music" <?= $preCat==='music'?'selected':'' ?>>Music</option>
                <option value="fitness" <?= $preCat==='fitness'?'selected':'' ?>>Fitness</option>
                <option value="cloud" <?= $preCat==='cloud'?'selected':'' ?>>Cloud / Storage</option>
                <option value="other" <?= $preCat==='other'?'selected':'' ?>>Other</option>
            </select>
            <button type="submit">Add Subscription</button>
        </form>
    </div>
</div>
</body>
</html>   