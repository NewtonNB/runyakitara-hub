<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

// Get statistics with growth calculations
$stats = [];
$growth = [];
$tables = ['lessons', 'dictionary', 'proverbs', 'articles', 'contact_messages', 'users'];

foreach ($tables as $table) {
    try {
        $result = $db->query("SELECT COUNT(*) as count FROM $table");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $stats[$table] = $row['count'];

        $lastMonth = $db->query("SELECT COUNT(*) as count FROM $table WHERE created_at < date('now', '-30 days')");
        $lastMonthRow = $lastMonth->fetch(PDO::FETCH_ASSOC);
        $lastMonthCount = $lastMonthRow['count'];

        if ($lastMonthCount > 0) {
            $growth[$table] = round((($stats[$table] - $lastMonthCount) / $lastMonthCount) * 100, 1);
        } else {
            $growth[$table] = $stats[$table] > 0 ? 100 : 0;
        }
    } catch (Exception $e) {
        $stats[$table] = 0;
        $growth[$table] = 0;
    }
}

foreach (['grammar_topics' => 'grammar', 'translations' => 'translations', 'media' => 'media'] as $tbl => $key) {
    try {
        $row = $db->query("SELECT COUNT(*) as count FROM $tbl")->fetch(PDO::FETCH_ASSOC);
        $stats[$key] = $row['count'];
    } catch (Exception $e) { $stats[$key] = 0; }
}

try {
    $row = $db->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'")->fetch(PDO::FETCH_ASSOC);
    $stats['new_messages'] = $row['count'];
} catch (Exception $e) { $stats['new_messages'] = 0; }

// Notifications
$notifications = [];
$notificationCount = 0;
try {
    $stmt = $db->query("SELECT id, name, subject, created_at FROM contact_messages WHERE status = 'new' ORDER BY created_at DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = ['type'=>'message','icon'=>'envelope-fill','color'=>'info',
            'title'=>'New message from '.$row['name'],'description'=>$row['subject'],
            'time'=>$row['created_at'],'link'=>'messages-manage.php?id='.$row['id']];
    }
    $stmt = $db->query("SELECT title, created_at FROM lessons WHERE created_at > datetime('now', '-1 day') ORDER BY created_at DESC LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = ['type'=>'lesson','icon'=>'book-fill','color'=>'primary',
            'title'=>'New lesson added','description'=>$row['title'],
            'time'=>$row['created_at'],'link'=>'lessons-manage.php'];
    }
    usort($notifications, fn($a,$b) => strtotime($b['time']) - strtotime($a['time']));
    $notifications = array_slice($notifications, 0, 10);
    $notificationCount = count($notifications);
} catch (Exception $e) { $notifications = []; $notificationCount = 0; }

// Recent activity
$recentActivity = [];
try {
    $a = $db->query("SELECT 'lesson' as type, title as name, created_at FROM lessons ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    $b = $db->query("SELECT 'article' as type, title as name, created_at FROM articles ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    $c = $db->query("SELECT 'word' as type, word_runyakitara as name, created_at FROM dictionary ORDER BY created_at DESC LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
    $d = $db->query("SELECT 'proverb' as type, substr(proverb, 1, 50) as name, created_at FROM proverbs ORDER BY created_at DESC LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
    $recentActivity = array_merge($a, $b, $c, $d);
    usort($recentActivity, fn($x,$y) => strtotime($y['created_at']) - strtotime($x['created_at']));
    $recentActivity = array_slice($recentActivity, 0, 10);
} catch (Exception $e) { $recentActivity = []; }

// Recent messages
$recentMessages = [];
try {
    $recentMessages = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $recentMessages = []; }

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Runyakitara Hub Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin-responsive.css">
</head>
<body class="admin-body">
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-logo">
                <i class="bi bi-translate"></i>
                <span>Runyakitara Hub</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="dashboard.php" class="nav-item active"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Content</div>
                <a href="lessons-manage.php" class="nav-item"><i class="bi bi-book"></i><span>Lessons</span></a>
                <a href="dictionary-manage.php" class="nav-item"><i class="bi bi-journal-text"></i><span>Dictionary</span></a>
                <a href="proverbs-manage.php" class="nav-item"><i class="bi bi-chat-quote"></i><span>Proverbs</span></a>
                <a href="grammar-manage.php" class="nav-item"><i class="bi bi-pencil-square"></i><span>Grammar</span></a>
                <a href="articles-manage.php" class="nav-item"><i class="bi bi-newspaper"></i><span>Articles</span></a>
                <a href="media-manage.php" class="nav-item"><i class="bi bi-play-circle"></i><span>Media</span></a>
                <a href="translations-manage.php" class="nav-item"><i class="bi bi-arrow-left-right"></i><span>Translations</span></a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Communication</div>
                <a href="messages-manage.php" class="nav-item">
                    <i class="bi bi-envelope"></i><span>Messages</span>
                    <?php if ($stats['new_messages'] > 0): ?>
                        <span class="nav-badge"><?php echo $stats['new_messages']; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">System</div>
                <a href="users-manage.php" class="nav-item"><i class="bi bi-people"></i><span>Users</span></a>
                <?php if (isset($_SESSION['user_roles']) && in_array('super_admin', $_SESSION['user_roles'])): ?>
                <a href="roles-manage.php" class="nav-item"><i class="bi bi-shield-check"></i><span>Roles & Permissions</span></a>
                <?php endif; ?>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar"><i class="bi bi-person-circle"></i></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
                    <div class="user-role"><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></div>
                </div>
            </div>
            <a href="logout.php" class="nav-item" style="margin-top:10px;">
                <i class="bi bi-box-arrow-right"></i><span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="mobile-toggle" id="mobileToggle"><i class="bi bi-list"></i></button>
                <div>
                    <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
                    <div class="breadcrumb">
                        <a href="dashboard.php">Home</a>
                        <i class="bi bi-chevron-right"></i>
                        <span>Dashboard</span>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <div class="notification-wrapper">
                        <button class="header-action" id="notificationBtn">
                            <i class="bi bi-bell"></i>
                            <?php if ($notificationCount > 0): ?>
                                <span class="notification-badge"><?php echo $notificationCount; ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h4>Notifications</h4>
                                <span class="notification-count"><?php echo $notificationCount; ?> new</span>
                            </div>
                            <div class="notification-list">
                                <?php if (!empty($notifications)): ?>
                                    <?php foreach ($notifications as $notif): ?>
                                        <a href="<?php echo $notif['link']; ?>" class="notification-item">
                                            <div class="notification-icon <?php echo $notif['color']; ?>">
                                                <i class="bi bi-<?php echo $notif['icon']; ?>"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                                                <div class="notification-description"><?php echo htmlspecialchars($notif['description']); ?></div>
                                                <div class="notification-time">
                                                    <?php
                                                    $t = strtotime($notif['time']); $diff = time() - $t;
                                                    if ($diff < 60) echo 'Just now';
                                                    elseif ($diff < 3600) echo floor($diff/60).' min ago';
                                                    elseif ($diff < 86400) echo floor($diff/3600).' hrs ago';
                                                    else echo date('M d, Y', $t);
                                                    ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="notification-empty">
                                        <i class="bi bi-bell-slash"></i>
                                        <p>No new notifications</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="notification-footer">
                                <a href="messages-manage.php">View all notifications</a>
                            </div>
                        </div>
                    </div>
                    <a href="../index.php" class="header-action" title="View Website"><i class="bi bi-eye"></i></a>
                </div>
            </div>
        </header>

        <!-- Main Area -->
        <main class="admin-main">

            <!-- Quick Actions -->
            <div class="quick-actions-section">
                <h3><i class="bi bi-lightning-charge"></i> Quick Actions</h3>
                <div class="quick-actions-grid">
                    <button class="quick-action-btn" onclick="location.href='lessons-manage.php?action=add'">
                        <i class="bi bi-plus-circle"></i><span>Add Lesson</span>
                    </button>
                    <button class="quick-action-btn" onclick="location.href='dictionary-manage.php?action=add'">
                        <i class="bi bi-plus-circle"></i><span>Add Word</span>
                    </button>
                    <button class="quick-action-btn" onclick="location.href='proverbs-manage.php?action=add'">
                        <i class="bi bi-plus-circle"></i><span>Add Proverb</span>
                    </button>
                    <button class="quick-action-btn" onclick="location.href='articles-manage.php?action=add'">
                        <i class="bi bi-plus-circle"></i><span>Add Article</span>
                    </button>
                    <button class="quick-action-btn" onclick="location.href='messages-manage.php'">
                        <i class="bi bi-envelope"></i><span>View Messages</span>
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <a href="lessons-manage.php" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-header">
                        <div class="stat-icon lessons"><i class="bi bi-book-fill"></i></div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Total Lessons</div>
                        <div class="stat-number"><?php echo $stats['lessons']; ?></div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-change <?php echo $growth['lessons'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $growth['lessons'] >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo abs($growth['lessons']); ?>%</span>
                        </div>
                        <span style="color:var(--text-light);">this month</span>
                    </div>
                </a>

                <a href="dictionary-manage.php" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-header">
                        <div class="stat-icon dictionary"><i class="bi bi-journal-text"></i></div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Dictionary Words</div>
                        <div class="stat-number"><?php echo $stats['dictionary']; ?></div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-change <?php echo $growth['dictionary'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $growth['dictionary'] >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo abs($growth['dictionary']); ?>%</span>
                        </div>
                        <span style="color:var(--text-light);">this month</span>
                    </div>
                </a>

                <a href="proverbs-manage.php" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-header">
                        <div class="stat-icon proverbs"><i class="bi bi-chat-quote-fill"></i></div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Proverbs</div>
                        <div class="stat-number"><?php echo $stats['proverbs']; ?></div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-change <?php echo $growth['proverbs'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $growth['proverbs'] >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo abs($growth['proverbs']); ?>%</span>
                        </div>
                        <span style="color:var(--text-light);">this month</span>
                    </div>
                </a>

                <a href="articles-manage.php" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-header">
                        <div class="stat-icon articles"><i class="bi bi-newspaper"></i></div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Articles</div>
                        <div class="stat-number"><?php echo $stats['articles']; ?></div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-change <?php echo $growth['articles'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $growth['articles'] >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo abs($growth['articles']); ?>%</span>
                        </div>
                        <span style="color:var(--text-light);">this month</span>
                    </div>
                </a>

                <a href="messages-manage.php" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-header">
                        <div class="stat-icon messages"><i class="bi bi-envelope-fill"></i></div>
                        <?php if ($stats['new_messages'] > 0): ?>
                            <span class="stat-badge"><?php echo $stats['new_messages']; ?> New</span>
                        <?php endif; ?>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Messages</div>
                        <div class="stat-number"><?php echo $stats['contact_messages']; ?></div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-change <?php echo $growth['contact_messages'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $growth['contact_messages'] >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo abs($growth['contact_messages']); ?>%</span>
                        </div>
                        <span style="color:var(--text-light);">this month</span>
                    </div>
                </a>

                <a href="users-manage.php" class="stat-card" style="text-decoration:none;color:inherit;">
                    <div class="stat-header">
                        <div class="stat-icon users"><i class="bi bi-people-fill"></i></div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Admin Users</div>
                        <div class="stat-number"><?php echo $stats['users']; ?></div>
                    </div>
                    <div class="stat-footer">
                        <div class="stat-change positive">
                            <i class="bi bi-check-circle"></i><span>Active</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
                <div class="chart-card">
                    <div class="card-header">
                        <h3><i class="bi bi-bar-chart"></i> Content Overview</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="contentChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="card-header">
                        <h3><i class="bi bi-pie-chart"></i> Distribution</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Activity Row -->
            <div class="activity-row">
                <div class="activity-card">
                    <div class="card-header">
                        <h3><i class="bi bi-clock-history"></i> Recent Activity</h3>
                        <select class="activity-filter" onchange="filterActivity(this.value)">
                            <option value="all">All Activity</option>
                            <option value="lesson">Lessons</option>
                            <option value="article">Articles</option>
                            <option value="word">Dictionary</option>
                            <option value="proverb">Proverbs</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentActivity)): ?>
                            <div class="activity-list">
                                <?php foreach ($recentActivity as $activity): ?>
                                    <div class="activity-item" data-type="<?php echo $activity['type']; ?>">
                                        <div class="activity-icon <?php echo $activity['type']; ?>">
                                            <?php $icons = ['lesson'=>'book-fill','article'=>'newspaper','word'=>'journal-text','proverb'=>'chat-quote-fill']; ?>
                                            <i class="bi bi-<?php echo $icons[$activity['type']] ?? 'file'; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title"><?php echo htmlspecialchars($activity['name']); ?></div>
                                            <div class="activity-meta">
                                                <?php echo ucfirst($activity['type']); ?> •
                                                <?php
                                                $t = strtotime($activity['created_at']); $diff = time() - $t;
                                                if ($diff < 3600) echo floor($diff/60).' minutes ago';
                                                elseif ($diff < 86400) echo floor($diff/3600).' hours ago';
                                                else echo date('M d, Y', $t);
                                                ?>
                                            </div>
                                        </div>
                                        <span class="activity-badge">New</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-clock-history"></i>
                                <p>No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="activity-card">
                    <div class="card-header">
                        <h3><i class="bi bi-envelope"></i> Recent Messages</h3>
                        <a href="messages-manage.php" class="view-all">View All <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentMessages)): ?>
                            <div class="activity-list">
                                <?php foreach ($recentMessages as $msg): ?>
                                    <div class="activity-item" onclick="location.href='messages-manage.php?id=<?php echo $msg['id']; ?>'" style="cursor:pointer;">
                                        <div class="activity-icon message">
                                            <i class="bi bi-envelope<?php echo $msg['status'] === 'new' ? '-fill' : ''; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title"><?php echo htmlspecialchars($msg['subject']); ?></div>
                                            <div class="activity-meta">
                                                From: <?php echo htmlspecialchars($msg['name']); ?> •
                                                <?php
                                                $t = strtotime($msg['created_at']); $diff = time() - $t;
                                                if ($diff < 3600) echo floor($diff/60).' min ago';
                                                elseif ($diff < 86400) echo floor($diff/3600).' hrs ago';
                                                else echo date('M d', $t);
                                                ?>
                                            </div>
                                        </div>
                                        <span class="activity-status <?php echo $msg['status']; ?>"><?php echo ucfirst($msg['status']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>No messages yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    document.getElementById('mobileToggle')?.addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });

    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    notificationBtn?.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.classList.toggle('active');
    });
    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target) && e.target !== notificationBtn) {
            notificationDropdown.classList.remove('active');
        }
    });
    notificationDropdown?.addEventListener('click', e => e.stopPropagation());

    function filterActivity(type) {
        document.querySelectorAll('.activity-item[data-type]').forEach(item => {
            item.style.display = (type === 'all' || item.dataset.type === type) ? 'flex' : 'none';
        });
    }

    // Content Overview Chart
    const contentCtx = document.getElementById('contentChart');
    if (contentCtx) {
        new Chart(contentCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Lessons', 'Dictionary', 'Proverbs', 'Articles', 'Messages'],
                datasets: [{
                    label: 'Count',
                    data: [<?php echo $stats['lessons']; ?>, <?php echo $stats['dictionary']; ?>, <?php echo $stats['proverbs']; ?>, <?php echo $stats['articles']; ?>, <?php echo $stats['contact_messages']; ?>],
                    backgroundColor: ['rgba(102,126,234,0.8)','rgba(240,147,251,0.8)','rgba(79,172,254,0.8)','rgba(67,233,123,0.8)','rgba(250,112,154,0.8)'],
                    borderColor: ['rgb(102,126,234)','rgb(240,147,251)','rgb(79,172,254)','rgb(67,233,123)','rgb(250,112,154)'],
                    borderWidth: 2, borderRadius: 8, borderSkipped: false
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
            }
        });
    }

    // Distribution Chart
    const distData = [<?php echo $stats['lessons']; ?>, <?php echo $stats['dictionary']; ?>, <?php echo $stats['proverbs']; ?>, <?php echo $stats['articles']; ?>];
    const distTotal = distData.reduce((a, b) => a + b, 0);
    const distCtx = document.getElementById('distributionChart');
    if (distCtx) {
        if (distTotal === 0) {
            distCtx.parentElement.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8;"><i class="bi bi-pie-chart" style="font-size:36px;display:block;margin-bottom:8px;"></i>No content yet</div>';
        } else {
            new Chart(distCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Lessons', 'Dictionary', 'Proverbs', 'Articles'],
                    datasets: [{
                        data: distData,
                        backgroundColor: ['rgba(102,126,234,0.85)','rgba(240,147,251,0.85)','rgba(79,172,254,0.85)','rgba(67,233,123,0.85)'],
                        borderColor: '#fff', borderWidth: 3, hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    plugins: { legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true } } },
                    cutout: '60%'
                }
            });
        }
    }
</script>
</body>
</html>
