<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);   
require_once 'auth.php';
require_login();

$user = get_user();

// If paid and sub_end has passed, extend by 1 month (PayPal auto-renewed)
if ($user['plan'] === 'paid' && !empty($user['sub_end']) && strtotime($user['sub_end']) <= time()) {
    $end = date('Y-m-d H:i:s', strtotime('+1 month'));
    $stmt = $pdo->prepare("UPDATE users SET sub_start = sub_end, sub_end = ? WHERE id = ?");
    $stmt->execute([$end, $_SESSION['user_id']]);
    $user = get_user();
}      
// Block dashboard if no active plan
$hasActiveSub = ($user['plan'] === 'paid' && !empty($user['sub_end']) && strtotime($user['sub_end']) > time());
$hasActiveTrial = ($user['plan'] === 'trial' && !empty($user['trial_end']) && strtotime($user['trial_end']) > time());

if (!$hasActiveSub && !$hasActiveTrial) {
    header('Location: onboarding.php');
    exit;
}   

function getServiceIcon($name) {
    $map = [
        'netflix' => 'netflix', 'spotify' => 'spotify', 'disney+' => 'disneyplus',
        'hulu' => 'hulu', 'hbo max' => 'hbomax', 'apple tv+' => 'appletv',
        'amazon prime video' => 'amazonprimevideo', 'paramount+' => 'paramountplus',
        'peacock' => 'peacock', 'crunchyroll' => 'crunchyroll', 'apple music' => 'applemusic',
        'tidal' => 'tidal', 'soundcloud' => 'soundcloud', 'icloud' => 'icloud',
        'google one' => 'google', 'dropbox' => 'dropbox', 'onedrive' => 'onedrive',
        'adobe creative cloud' => 'adobe', 'canva pro' => 'canva',
        'chatgpt plus' => 'openai', 'notion plus' => 'notion',
        'figma' => 'figma', 'slack' => 'slack', 'zoom' => 'zoom',
        'microsoft 365' => 'microsoft', 'github pro' => 'github',
        'peloton' => 'peloton', 'strava' => 'strava', 'twitch' => 'twitch',
        'reddit premium' => 'reddit', 'x premium' => 'x',
        'linkedin premium' => 'linkedin', 'duolingo super' => 'duolingo',
        'kindle unlimited' => 'kindle', 'audible' => 'audible',
        'roblox premium' => 'roblox', 'xbox game pass' => 'xbox',
        'playstation plus' => 'playstation', 'youtube premium' => 'youtube',
        'youtube music' => 'youtubemusic', 'newspaper / magazine' => 'newspaper',   
        'gym / fitness' => 'gym',
    ];
    $key = $map[strtolower($name)] ?? null;
    return $key ? "icons/$key.svg" : null;        
}   

function getServiceUrl($name) {
    $map = [
        'netflix' => 'https://www.netflix.com', 'spotify' => 'https://www.spotify.com',
        'youtube premium' => 'https://www.youtube.com/premium', 'disney+' => 'https://www.disneyplus.com',
        'hulu' => 'https://www.hulu.com', 'hbo max' => 'https://www.hbomax.com',
        'apple tv+' => 'https://tv.apple.com', 'amazon prime video' => 'https://www.primevideo.com',
        'paramount+' => 'https://www.paramountplus.com', 'peacock' => 'https://www.peacocktv.com',
        'crunchyroll' => 'https://www.crunchyroll.com', 'apple music' => 'https://music.apple.com',
        'tidal' => 'https://tidal.com', 'soundcloud' => 'https://soundcloud.com',
        'icloud' => 'https://www.icloud.com', 'google one' => 'https://one.google.com',
        'dropbox' => 'https://www.dropbox.com', 'onedrive' => 'https://onedrive.live.com',
        'adobe creative cloud' => 'https://www.adobe.com/creativecloud', 'canva pro' => 'https://www.canva.com',
        'chatgpt plus' => 'https://chat.openai.com', 'notion plus' => 'https://www.notion.so',
        'figma' => 'https://www.figma.com', 'slack' => 'https://slack.com',
        'zoom' => 'https://zoom.us', 'microsoft 365' => 'https://www.microsoft.com/microsoft-365',
        'github pro' => 'https://github.com', 'peloton' => 'https://www.onepeloton.com',
        'strava' => 'https://www.strava.com', 'twitch' => 'https://www.twitch.tv',
        'reddit premium' => 'https://www.reddit.com', 'x premium' => 'https://x.com',
        'linkedin premium' => 'https://www.linkedin.com', 'duolingo super' => 'https://www.duolingo.com',
        'kindle unlimited' => 'https://www.amazon.com/kindle-unlimited', 'audible' => 'https://www.audible.com',
        'roblox premium' => 'https://www.roblox.com', 'xbox game pass' => 'https://www.xbox.com',
        'playstation plus' => 'https://www.playstation.com',
    ];
    return $map[strtolower($name)] ?? '#';
}   

function getCancelUrl($name) {
    $map = [
        'netflix' => 'https://www.netflix.com/cancelplan',
        'spotify' => 'https://www.spotify.com/us/account/tech',
        'youtube premium' => 'https://www.youtube.com/premium/cancel',
        'youtube music' => 'https://www.youtube.com/premium/cancel',
        'disney+' => 'https://www.disneyplus.com/help/cancel',
        'hulu' => 'https://www.hulu.com/start/cancel',
        'hbo max' => 'https://www.hbomax.com/account',
        'apple tv+' => 'https://1password.com/apple',
        'amazon prime video' => 'https://www.amazon.com/gp/css/hz/manageprime',
        'paramount+' => 'https://www.paramountplus.com/signin',
        'peacock' => 'https://www.peacocktv.com/subscriptions',
        'crunchyroll' => 'https://crunchyroll.com/account',
        'apple music' => 'https://music.apple.com',
        'tidal' => 'https://tidal.com/settings',
        'soundcloud' => 'https://soundcloud.com/settings',
        'icloud' => 'https://www.icloud.com/settings',
        'google one' => 'https://one.google.com/settings',
        'dropbox' => 'https://www.dropbox.com/account',
        'onedrive' => 'https://onedrive.live.com',
        'adobe creative cloud' => 'https://account.adobe.com/plans',
        'canva pro' => 'https://www.canva.com/account',
        'chatgpt plus' => 'https://chat.openai.com/settings',
        'notion plus' => 'https://www.notion.so/settings',
        'figma' => 'https://www.figma.com/settings',
        'slack' => 'https://slack.com/settings',
        'zoom' => 'https://zoom.us/account',
        'microsoft 365' => 'https://account.microsoft.com/services',
        'github pro' => 'https://github.com/settings/billing',
        'peloton' => 'https://www.onepeloton.com/mymembership',
        'strava' => 'https://www.strava.com/settings',
        'twitch' => 'https://www.twitch.tv/settings/subscriptions',
        'reddit premium' => 'https://www.reddit.com/settings',
        'x premium' => 'https://x.com/settings',
        'linkedin premium' => 'https://www.linkedin.com/settings',
        'duolingo super' => 'https://www.duolingo.com/settings',
        'kindle unlimited' => 'https://www.amazon.com/gp/kindle-unlimited',
        'audible' => 'https://www.audible.com/account',
        'roblox premium' => 'https://www.roblox.com/my/account',
        'xbox game pass' => 'https://account.xbox.com',
        'playstation plus' => 'https://account.playstation.com',
    ];
    return $map[strtolower($name)] ?? getServiceUrl($name);
}   

$stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? ORDER BY renewal_date ASC");
$stmt->execute([$_SESSION['user_id']]);
$subs = $stmt->fetchAll();
$total = array_sum(array_column($subs, 'price'));

// Category breakdown
$cats = ['streaming' => 0, 'music' => 0, 'fitness' => 0, 'cloud' => 0, 'other' => 0];
foreach ($subs as $s) {
    $cat = $s['category'] ?? 'other';
    if (!isset($cats[$cat])) $cats[$cat] = 0;
    $cats[$cat] += $s['price'];
}
$catLabels = json_encode(array_keys($cats));
$catData = json_encode(array_values($cats));

// Upcoming (next 7 days)
$upcoming = array_filter($subs, fn($s) => (strtotime($s['renewal_date']) - time()) < 7 * 86400 && (strtotime($s['renewal_date']) - time()) > -86400);
$upcomingCount = count($upcoming);

// Fun stat: how many coffees
$coffeeCost = 5;
$coffees = $total / $coffeeCost;
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="manifest" href="manifest.json">
	<script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">   
    <title>SubTracker — Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-body">
<nav>
    <span>SubTracker</span>
    <div class="nav-actions">
		<?php if ($user['plan'] === 'paid' && !empty($user['sub_end']) && strtotime($user['sub_end']) > time()): ?>
        <a href="upgrade.php" class="nav-btn"> Manage</a>
        <?php else: ?>
        <a href="upgrade.php" class="nav-btn upgrade-nav-btn">⚡ Upgrade</a>
        <?php endif; ?>           
        <a href="services.php" class="nav-btn">+ Add</a>   
        <a href="logout.php" class="nav-btn">Logout (<?= htmlspecialchars($user['name']) ?>)</a>
    </div>
</nav>

<?php
// Payment expiry banner (1 week before sub_end)
if ($user['plan'] === 'paid' && $user['sub_end']):
    $subEnd = strtotime($user['sub_end']);
    $now2 = time();
    $daysToExpiry = ($subEnd - $now2) / 86400;
    if ($daysToExpiry > 0 && $daysToExpiry <= 7):
        $totalSeconds = $subEnd - $now2;
        $d = floor($totalSeconds / 86400);
        $h = floor(($totalSeconds % 86400) / 3600);
        $m = floor(($totalSeconds % 3600) / 60);
        $s = $totalSeconds % 60;
?>
<div class="expiry-banner">
    <span class="expiry-text">⚠️ Your subscription renews in <strong><?= $d ?>d <?= $h ?>h <?= $m ?>m <?= $s ?>s</strong></span>
    <a href="upgrade.php" class="expiry-btn">Renew Now</a>
</div>
<?php
    endif;
endif;
?>   

<div class="dash-container">

<?php if (isset($_GET['deleted'])): ?>
<div class="toast" id="toast">✅ <?= htmlspecialchars($_GET['deleted']) ?> deleted</div>
<script>
history.replaceState({}, '', 'index.php');
setTimeout(() => {
    const t = document.getElementById('toast');
    t.style.opacity = '0';
    setTimeout(() => t.remove(), 300);
}, 3000);
</script>
<?php endif; ?>    

    <!-- Top stats row -->
    <div class="dash-stats">
        <div class="dash-card clickable" onclick="document.querySelector('.charts-row').scrollIntoView({behavior:'smooth'})">
            <div class="dash-card-icon"><img src="icons/cash.svg" alt="Cash"></div>
            <div class="dash-card-value">$<?= number_format($total, 2) ?></div>
            <div class="dash-card-label">per month</div>
            <div class="dash-card-sub">Click to see breakdown ↓</div>
        </div>
        <div class="dash-card clickable" onclick="document.querySelector('.table-section').scrollIntoView({behavior:'smooth'})">
            <div class="dash-card-icon"><img src="icons/bar.svg" alt="Bar"></div>
            <div class="dash-card-value"><?= count($subs) ?></div>
            <div class="dash-card-label">subscriptions</div>   
            <div class="dash-card-sub">Click to see categories ↓</div>
        </div>
        <div class="dash-card clickable" onclick="document.querySelector('.widgets-row').scrollIntoView({behavior:'smooth'})">
            <div class="dash-card-icon"><img src="icons/calendar.svg" alt="Calender"></div>
            <div class="dash-card-value">$<?= number_format($total * 12, 2) ?></div>
            <div class="dash-card-label">per year</div>
            <div class="dash-card-sub">Click to see yearly view ↓</div>
        </div>
        <div class="dash-card clickable" onclick="document.querySelector('.widgets-row').scrollIntoView({behavior:'smooth'})">
            <div class="dash-card-icon"><img src="icons/coffee.svg" alt="Coffee"></div>
            <div class="dash-card-value"><?= number_format($coffees, 0) ?></div>
            <div class="dash-card-label">coffees / month</div>
            <div class="dash-card-sub">Click for fun facts ↓</div>
        </div>
    </div>

    <!-- Alert bar -->
    <?php if ($upcomingCount > 0): ?>
    <div class="dash-alert" id="alertBar">
        <span class="alert-pulse"></span>
        <strong><?= $upcomingCount ?> renewal<?= $upcomingCount > 1 ? 's' : '' ?> coming up!</strong>
        <span class="alert-detail">
            <?php foreach (array_slice($upcoming, 0, 3) as $s): ?>
                <?= htmlspecialchars($s['name']) ?> ($<?= $s['price'] ?>) · 
            <?php endforeach; ?>
        </span>
        <button class="alert-dismiss" onclick="document.getElementById('alertBar').style.display='none'">✕</button>
    </div>
    <?php endif; ?>

    <!-- Charts row -->
    <div class="charts-row">
        <div class="chart-box" id="chartSection">
            <h3>Monthly Spending by Category</h3>
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-box" id="donutSection">
            <h3>Where Your Money Goes</h3>
            <canvas id="donutChart"></canvas>
        </div>
    </div>

    <!-- Interactive widgets row -->
    <div class="widgets-row">
        <div class="widget" id="yearSection">
            <h3> Yearly Cost</h3>
            <div class="year-bar">
                <div class="year-bar-fill" style="width: <?= $total > 0 ? min(100, ($total * 12) / ($total * 12 * 1.5) * 100) : 0 ?>%"></div>
            </div>
            <p class="year-label">$<?= number_format($total * 12, 2) ?> / year</p>
            <p class="year-sub">Budget target: $<?= number_format($total * 12 * 1.5, 0) ?> (150% of current)</p>
            <p class="year-sub" style="margin-top:6px; font-size:11px; color:#94a3b8;">Your budget auto-scales to 1.5× your current spending. If you add more subs, it grows. If you cancel, it shrinks.</p>
        </div>

        <div class="widget" id="funSection">
            <h3> Fun Fact</h3>
            <p class="fun-fact" id="funFactText">Loading...</p>
        </div>

        <div class="widget">
            <h3> What if you cancel...</h3>
            <p class="tip-text" id="saveSubName">—</p>
            <div class="save-amount" id="saveAmount">$0/mo</div>
            <p class="tip-sub" id="saveYearAmount">= $0/year back in your pocket</p>
            <p class="fact-rotate-hint" style="margin-top:10px;">↻ Cycles through your subs every 4s</p>
        </div>
    </div>   

    <!-- Subscriptions table -->
    <div class="table-section">
        <div class="table-header">
            <h3>Your Subscriptions</h3>
            <div class="table-filters">
                <button class="filter-btn active" onclick="filterTable('all', this)">All</button>
                <button class="filter-btn" onclick="filterTable('streaming', this)">Streaming</button>
                <button class="filter-btn" onclick="filterTable('music', this)">Music</button>
                <button class="filter-btn" onclick="filterTable('fitness', this)">Fitness</button>
                <button class="filter-btn" onclick="filterTable('cloud', this)">Cloud</button>
            </div>
        </div>
        <table id="subTable">
            <thead>
                <tr><th>Name</th><th>Price</th><th>Renews</th><th>Category</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($subs as $s): ?>
                <tr data-category="<?= $s['category'] ?>">
                    <td class="sub-name">
                        <a href="<?= htmlspecialchars(getServiceUrl($s['name'])) ?>" target="_blank" class="sub-link" title="Open <?= htmlspecialchars($s['name']) ?>">
                            <?php $iconFile = getServiceIcon($s['name']); ?>
                            <?php if ($iconFile): ?>
                                <img src="<?= $iconFile ?>" class="sub-icon-img" alt="<?= htmlspecialchars($s['name']) ?>">
                            <?php else: ?>
                                <span class="sub-icon" style="background: #64748b"><?= substr($s['name'], 0, 1) ?></span>
                            <?php endif; ?>   
                            <?= htmlspecialchars($s['name']) ?>
                        </a>
                    </td>   
                    <td>$<?= number_format($s['price'], 2) ?></td>
                    <td><?= $s['renewal_date'] ?></td>
                    <td><span class="cat-badge cat-<?= $s['category'] ?>"><?= ucfirst($s['category']) ?></span></td>
                    <td class="actions-cell">
                        <a href="<?= getCancelUrl($s['name']) ?>" target="_blank" class="action-btn cancel-btn" title="Cancel on site">✂️</a>
                        <a href="edit.php?id=<?= $s['id'] ?>" class="action-btn edit-btn" title="Edit">✏️</a>
                        <form method="POST" action="delete.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" class="action-btn del-btn" title="Remove from tracker" style="background:none;border:none;cursor:pointer;font-size:16px;">🗑️</button>
                        </form>
                    </td>      
                </tr>
                <?php endforeach; ?>
                <?php if (!count($subs)): ?><tr><td colspan="5" class="empty-row">No subscriptions yet. Click "+ Add" to get started!</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
// Bar chart
const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: <?= $catLabels ?>,
        datasets: [{
            label: 'Monthly ($)',
            data: <?= $catData ?>,
            backgroundColor: ['#e50914', '#1db954', '#6366f1', '#0ea5e9', '#f59e0b'],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});

// Donut chart
const donutCtx = document.getElementById('donutChart').getContext('2d');
new Chart(donutCtx, {
    type: 'doughnut',
    data: {
        labels: <?= $catLabels ?>,
        datasets: [{
            data: <?= $catData ?>,
            backgroundColor: ['#e50914', '#1db954', '#6366f1', '#0ea5e9', '#f59e0b'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } }
        }
    }
});

// Fun facts — auto-rotates every 12 seconds
const facts = [
    "The average person has 12 subscriptions they don't fully use.",
    "Netflix raised its price 4 times since 2020. That's $38/year extra.",
    "Most people forget about 1-2 subscriptions per year.",
    "Switching to an annual plan saves ~2 months of cost on most services.",
    "The average US household spends $200+/month on subscriptions.",
    "Spotify Premium costs $132/year — that's 6 movie tickets.",
    "iCloud 200GB costs $120/year for storage you probably don't need.",
    "Gym memberships are the #1 forgotten subscription in the US.",
    "Disney+ + Hulu + ESPN+ bundle is cheaper than 3 separate subscriptions.",
    "The average person spends $1,440/year on streaming alone.",
    "You could buy a new phone with 2 years of forgotten subscriptions.",
    "Annual billing saves 15-20% on most services vs monthly.",
    "The average person subscribes to 2-3 music streaming services without realising.",
    "Adobe Creative Cloud is $599/year — that's $50/month you may not need.",
    "Halving your subscriptions is easier than earning an extra $500/month.",
    "The average subscription price increase per year is 8-12%.",
    "One forgotten $15/month sub costs $180/year — that's a holiday dinner.",
    "People who track subscriptions save an average of $120/year.",
    "Your most expensive sub is probably one you use the least.",
    "Cloud storage is the #2 most forgotten subscription after gyms.",
    "Switching from monthly to annual on 3 subs saves ~$90/year.",
    "The average person has 4 streaming services but only watches 2.",
    "A $10/month sub is $120/year — that's 24 coffees.",
    "Most people would notice if their phone cost $200 more, but not their subs.",
    "Subscription fatigue is real — 60% of users cancel at least one per year.",
    "The cheapest way to save money is the one you already have: awareness."
];
let factIdx = Math.floor(Math.random() * facts.length);
const factEl = document.getElementById('funFactText');
factEl.textContent = facts[factIdx];

function rotateFact() {
    let next;
    do { next = Math.floor(Math.random() * facts.length); } while (next === factIdx);
    factIdx = next;
    factEl.style.opacity = '0';
    setTimeout(() => {
        factEl.textContent = facts[factIdx];
        factEl.style.opacity = '1';
    }, 300);
}
setInterval(rotateFact, 12000);
factEl.style.transition = 'opacity 0.3s';

// Save Money — cycles through each sub
const subs = <?= json_encode(array_map(fn($s) => ['name' => $s['name'], 'price' => (float)$s['price']], $subs)) ?>;
const totalMonthly = <?= $total ?>;
let saveIdx = 0;
const saveEl = document.getElementById('saveAmount');
const saveSubEl = document.getElementById('saveSubName');
const saveYearEl = document.getElementById('saveYearAmount');

function cycleSave() {
    if (subs.length === 0) return;
    const sub = subs[saveIdx % subs.length];
    const remaining = totalMonthly - sub.price;
    saveEl.textContent = '$' + sub.price.toFixed(2) + '/mo';
    saveSubEl.textContent = sub.name;
    saveYearEl.textContent = '$' + (sub.price * 12).toFixed(2) + '/year';
    saveIdx++;
}
cycleSave();
setInterval(cycleSave, 4000);

// Table filter
function filterTable(cat, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#subTable tbody tr').forEach(row => {
        if (cat === 'all' || row.dataset.category === cat) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
<?php require_once 'notify.php'; $unread = get_unread_count($_SESSION['user_id']); ?>
<a href="notifications.php" class="bell-float" title="Notifications">
    🔔
    <?php if ($unread > 0): ?>
    <span class="bell-badge"><?= $unread ?></span>
    <?php endif; ?>
</a>
<span class="bell-float-text">Alerts</span>   
<a href="help.php" class="help-float" title="Help & Support">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
</a>
<span class="help-float-text">Help</span>
</body>
</html>     