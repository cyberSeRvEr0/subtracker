<?php
require_once 'auth.php';
require_login();

function getServiceIcon($name) {
    $map = [
        'netflix' => 'netflix', 'spotify' => 'spotify', 'youtube premium' => 'youtube',
        'youtube music' => 'youtubemusic', 'disney+' => 'disneyplus', 'hulu' => 'hulu',
        'hbo max' => 'hbomax', 'apple tv+' => 'appletv', 'amazon prime video' => 'amazonprimevideo',
        'paramount+' => 'paramountplus', 'peacock' => 'peacock', 'crunchyroll' => 'crunchyroll',
        'apple music' => 'applemusic', 'tidal' => 'tidal', 'soundcloud' => 'soundcloud',
        'icloud' => 'icloud', 'google one' => 'google', 'dropbox' => 'dropbox',
        'onedrive' => 'onedrive', 'adobe creative cloud' => 'adobe', 'canva pro' => 'canva',
        'chatgpt plus' => 'openai', 'notion plus' => 'notion', 'figma' => 'figma',
        'slack' => 'slack', 'zoom' => 'zoom', 'microsoft 365' => 'microsoft',
        'github pro' => 'github', 'peloton' => 'peloton', 'strava' => 'strava',
        'twitch' => 'twitch', 'reddit premium' => 'reddit', 'x premium' => 'x',
        'linkedin premium' => 'linkedin', 'duolingo super' => 'duolingo',
        'kindle unlimited' => 'kindle', 'audible' => 'audible', 'roblox premium' => 'roblox',
        'xbox game pass' => 'xbox', 'playstation plus' => 'playstation',
        'gym / fitness' => 'gym', 'newspaper / magazine' => 'newspaper',   
    ];
    $key = $map[strtolower($name)] ?? null;
    return $key ? (substr($key, -4) === '.png' ? "icons/$key" : "icons/$key.svg") : null;      
}   
$services = [
    ['name' => 'Netflix', 'icon' => 'N', 'color' => '#e50914', 'url' => 'https://www.netflix.com', 'cat' => 'streaming'],
    ['name' => 'Spotify', 'icon' => 'S', 'color' => '#1db954', 'url' => 'https://www.spotify.com', 'cat' => 'music'],
    ['name' => 'YouTube Premium', 'icon' => '▶', 'color' => '#ff0000', 'url' => 'https://www.youtube.com/premium', 'cat' => 'streaming'],
    ['name' => 'Disney+', 'icon' => 'D', 'color' => '#113ccf', 'url' => 'https://www.disneyplus.com', 'cat' => 'streaming'],
    ['name' => 'Hulu', 'icon' => 'H', 'color' => '#1ce783', 'url' => 'https://www.hulu.com', 'cat' => 'streaming'],
    ['name' => 'HBO Max', 'icon' => 'H', 'color' => '#991eeb', 'url' => 'https://www.hbomax.com', 'cat' => 'streaming'],
    ['name' => 'Apple TV+', 'icon' => '', 'color' => '#fac51c', 'url' => 'https://tv.apple.com', 'cat' => 'streaming'],
    ['name' => 'Amazon Prime Video', 'icon' => 'a', 'color' => '#00a8e1', 'url' => 'https://www.primevideo.com', 'cat' => 'streaming'],
    ['name' => 'Paramount+', 'icon' => 'P', 'color' => '#0064ff', 'url' => 'https://www.paramountplus.com', 'cat' => 'streaming'],
    ['name' => 'Peacock', 'icon' => 'P', 'color' => '#0e7490', 'url' => 'https://www.peacocktv.com', 'cat' => 'streaming'],
    ['name' => 'Crunchyroll', 'icon' => 'C', 'color' => '#f47521', 'url' => 'https://www.crunchyroll.com', 'cat' => 'streaming'],
    ['name' => 'Apple Music', 'icon' => '♫', 'color' => '#fac51c', 'url' => 'https://music.apple.com', 'cat' => 'music'],
    ['name' => 'Tidal', 'icon' => 'T', 'color' => '#1675ff', 'url' => 'https://tidal.com', 'cat' => 'music'],
    ['name' => 'SoundCloud', 'icon' => 'S', 'color' => '#ff5500', 'url' => 'https://soundcloud.com', 'cat' => 'music'],
    ['name' => 'iCloud', 'icon' => '☁', 'color' => '#3693f3', 'url' => 'https://www.icloud.com', 'cat' => 'cloud'],
    ['name' => 'Google One', 'icon' => 'G', 'color' => '#4285f4', 'url' => 'https://one.google.com', 'cat' => 'cloud'],
    ['name' => 'Dropbox', 'icon' => 'D', 'color' => '#0061ff', 'url' => 'https://www.dropbox.com', 'cat' => 'cloud'],
    ['name' => 'OneDrive', 'icon' => 'O', 'color' => '#0078d4', 'url' => 'https://onedrive.live.com', 'cat' => 'cloud'],
    ['name' => 'Adobe Creative Cloud', 'icon' => 'A', 'color' => '#ff0000', 'url' => 'https://www.adobe.com/creativecloud', 'cat' => 'other'],
    ['name' => 'Canva Pro', 'icon' => 'C', 'color' => '#00c4cc', 'url' => 'https://www.canva.com', 'cat' => 'other'],
    ['name' => 'ChatGPT Plus', 'icon' => 'G', 'color' => '#10a37f', 'url' => 'https://chat.openai.com', 'cat' => 'other'],
    ['name' => 'Notion Plus', 'icon' => 'N', 'color' => '#000000', 'url' => 'https://www.notion.so', 'cat' => 'other'],
    ['name' => 'Figma', 'icon' => 'F', 'color' => '#a259ff', 'url' => 'https://www.figma.com', 'cat' => 'other'],
    ['name' => 'Slack', 'icon' => 'S', 'color' => '#611f69', 'url' => 'https://slack.com', 'cat' => 'other'],
    ['name' => 'Zoom', 'icon' => 'Z', 'color' => '#2d8cff', 'url' => 'https://zoom.us', 'cat' => 'other'],
    ['name' => 'Microsoft 365', 'icon' => 'M', 'color' => '#d83b01', 'url' => 'https://www.microsoft.com/microsoft-365', 'cat' => 'other'],
    ['name' => 'GitHub Pro', 'icon' => 'G', 'color' => '#333333', 'url' => 'https://github.com', 'cat' => 'other'],
    ['name' => 'Gym / Fitness', 'icon' => '🏋', 'color' => '#f59e0b', 'url' => '#', 'cat' => 'fitness'],
    ['name' => 'Peloton', 'icon' => 'P', 'color' => '#df1c2f', 'url' => 'https://www.onepeloton.com', 'cat' => 'fitness'],
    ['name' => 'Strava', 'icon' => 'S', 'color' => '#fc4c02', 'url' => 'https://www.strava.com', 'cat' => 'fitness'],
    ['name' => 'Newspaper / Magazine', 'icon' => '📰', 'color' => '#64748b', 'url' => '#', 'cat' => 'other'],
    ['name' => 'Twitch', 'icon' => 'T', 'color' => '#9146ff', 'url' => 'https://www.twitch.tv', 'cat' => 'streaming'],
    ['name' => 'Reddit Premium', 'icon' => 'R', 'color' => '#ff4500', 'url' => 'https://www.reddit.com', 'cat' => 'other'],
    ['name' => 'X Premium', 'icon' => 'X', 'color' => '#000000', 'url' => 'https://x.com', 'cat' => 'other'],
    ['name' => 'LinkedIn Premium', 'icon' => 'in', 'color' => '#0a66c2', 'url' => 'https://www.linkedin.com', 'cat' => 'other'],
    ['name' => 'Duolingo Super', 'icon' => 'D', 'color' => '#58cc02', 'url' => 'https://www.duolingo.com', 'cat' => 'other'],
    ['name' => 'Kindle Unlimited', 'icon' => 'K', 'color' => '#ff9900', 'url' => 'https://www.amazon.com/kindle-unlimited', 'cat' => 'other'],
    ['name' => 'Audible', 'icon' => 'A', 'color' => '#f8991c', 'url' => 'https://www.audible.com', 'cat' => 'other'],
    ['name' => 'Roblox Premium', 'icon' => 'R', 'color' => '#000000', 'url' => 'https://www.roblox.com', 'cat' => 'other'],
    ['name' => 'Xbox Game Pass', 'icon' => 'X', 'color' => '#107c10', 'url' => 'https://www.xbox.com', 'cat' => 'other'],
    ['name' => 'PlayStation Plus', 'icon' => 'P', 'color' => '#0070d1', 'url' => 'https://www.playstation.com', 'cat' => 'other'],
    ['name' => 'YouTube Music', 'icon' => '▶', 'color' => '#ff0000', 'url' => 'https://music.youtube.com', 'cat' => 'music'],   
];
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>   
    <link rel="icon" type="image/svg+xml" href="favicon.svg">   
    <title>SubTracker — Add a Service</title>
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
    <div class="services-header">
        <h2>Add a Subscription</h2>
        <p class="services-sub">Search for your service and click "Add" to get started</p>
        <input type="text" id="serviceSearch" placeholder="🔍  Search services..." oninput="filterServices()">
    </div>

    <div class="services-grid" id="servicesGrid">
        <?php foreach ($services as $s): ?>
        <div class="service-item" data-name="<?= strtolower($s['name']) ?>">
            <a href="<?= $s['url'] ?>" target="_blank" class="service-link">
                <?php $iconFile = getServiceIcon($s['name']); ?>
                <?php if ($iconFile): ?>
                    <img src="<?= $iconFile ?>" class="service-icon-img" alt="<?= htmlspecialchars($s['name']) ?>">
                <?php else: ?>
                    <div class="service-icon" style="background: <?= $s['color'] ?>;"><?= $s['icon'] ?></div>
                <?php endif; ?>
                <div class="service-info">
                    <span class="service-name"><?= htmlspecialchars($s['name']) ?></span>
                    <span class="service-cat"><?= ucfirst($s['cat']) ?></span>
                </div>
            </a>   
            <a href="add.php?service=<?= urlencode($s['name']) ?>&cat=<?= $s['cat'] ?>" class="service-add-btn">Add</a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="services-custom">
        <p>Can't find yours?</p>
        <a href="add.php" class="custom-add-btn">+ Add a custom subscription</a>
    </div>
</div>

<script>
function filterServices() {
    const q = document.getElementById('serviceSearch').value.toLowerCase();
    document.querySelectorAll('.service-item').forEach(item => {
        item.style.display = item.dataset.name.includes(q) ? '' : 'none';
    });
}
</script>
<a href="help.php" class="help-float" title="Help & Support">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
</a>
<span class="help-float-text">Help</span>      
</body>
</html>   