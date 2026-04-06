<?php
// Get notifications for header
if (!isset($db)) {
    require_once __DIR__ . '/../../config/database.php';
    $db = getDBConnection();
}

$headerNotifications = [];
$headerNotificationCount = 0;
try {
    // New messages
    $stmt = $db->query("SELECT id, name, subject, created_at FROM contact_messages WHERE status = 'new' ORDER BY created_at DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $headerNotifications[] = [
            'type' => 'message',
            'icon' => 'envelope-fill',
            'color' => 'info',
            'title' => 'New message from ' . $row['name'],
            'description' => $row['subject'],
            'time' => $row['created_at'],
            'link' => 'messages-manage.php?id=' . $row['id']
        ];
    }

    // Pending comments
    $stmt = $db->query("SELECT id, name, comment, created_at FROM comments WHERE status != 'approved' ORDER BY created_at DESC LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $headerNotifications[] = [
            'type' => 'comment',
            'icon' => 'chat-dots-fill',
            'color' => 'warning',
            'title' => 'Comment awaiting moderation',
            'description' => htmlspecialchars(mb_substr($row['comment'], 0, 60)),
            'time' => $row['created_at'],
            'link' => 'comments-manage.php'
        ];
    }
    
    // Recent content (last 24 hours)
    $stmt = $db->query("SELECT title, created_at FROM lessons WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY created_at DESC LIMIT 2");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $headerNotifications[] = [
            'type' => 'lesson',
            'icon' => 'book-fill',
            'color' => 'primary',
            'title' => 'New lesson added',
            'description' => $row['title'],
            'time' => $row['created_at'],
            'link' => 'lessons-manage.php'
        ];
    }
    
    $stmt = $db->query("SELECT title, created_at FROM articles WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY created_at DESC LIMIT 2");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $headerNotifications[] = [
            'type' => 'article',
            'icon' => 'newspaper',
            'color' => 'success',
            'title' => 'New article published',
            'description' => $row['title'],
            'time' => $row['created_at'],
            'link' => 'articles-manage.php'
        ];
    }
    
    usort($headerNotifications, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
    $headerNotifications = array_slice($headerNotifications, 0, 8);
    $headerNotificationCount = count($headerNotifications);
} catch (Exception $e) {
    $headerNotifications = [];
    $headerNotificationCount = 0;
}
?>
<header class="admin-header">
    <div class="header-left">
        <button class="mobile-toggle" id="mobileToggle">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <h1>
                <i class="bi bi-<?php 
                    $page = basename($_SERVER['PHP_SELF']);
                    echo match($page) {
                        'dashboard.php' => 'speedometer2',
                        'lessons-manage.php' => 'book',
                        'dictionary-manage.php' => 'journal-text',
                        'proverbs-manage.php' => 'chat-quote',
                        'grammar-manage.php' => 'pencil-square',
                        'articles-manage.php' => 'newspaper',
                        'media-manage.php' => 'play-circle',
                        'translations-manage.php' => 'arrow-left-right',
                        'messages-manage.php' => 'envelope',
                        'users-manage.php' => 'people',
                        'settings.php' => 'gear',
                        'analytics.php' => 'graph-up',
                        'comments-manage.php' => 'chat-dots',
                        default => 'speedometer2'
                    };
                ?>"></i>
                <?php 
                    echo match($page) {
                        'dashboard.php' => 'Dashboard',
                        'dictionary-manage.php' => 'Manage Dictionary',
                        'proverbs-manage.php' => 'Manage Proverbs',
                        'grammar-manage.php' => 'Manage Grammar',
                        'articles-manage.php' => 'Manage Articles',
                        'media-manage.php' => 'Manage Media',
                        'translations-manage.php' => 'Manage Translations',
                        'messages-manage.php' => 'Messages',
                        'users-manage.php' => 'Manage Users',
                        'settings.php' => 'Settings',
                        'analytics.php' => 'Analytics',
                        'comments-manage.php' => 'Manage Comments',
                        default => 'Admin Panel'
                    };
                ?>
            </h1>
            <div class="breadcrumb">
                <a href="dashboard.php">Home</a>
                <i class="bi bi-chevron-right"></i>
                <span><?php 
                    echo match($page) {
                        'dashboard.php' => 'Dashboard',
                        'lessons-manage.php' => 'Lessons',
                        'dictionary-manage.php' => 'Dictionary',
                        'proverbs-manage.php' => 'Proverbs',
                        'grammar-manage.php' => 'Grammar',
                        'articles-manage.php' => 'Articles',
                        'media-manage.php' => 'Media',
                        'translations-manage.php' => 'Translations',
                        'messages-manage.php' => 'Messages',
                        'users-manage.php' => 'Users',
                        'settings.php' => 'Settings',
                        'analytics.php' => 'Analytics',
                        'comments-manage.php' => 'Comments',
                        default => 'Page'
                    };
                ?></span>
            </div>
        </div>
    </div>
    <div class="header-right">
        <div class="header-search">
            <input type="text" placeholder="Search...">
            <i class="bi bi-search"></i>
        </div>
        <div class="header-actions">
            <div class="notification-wrapper">
                <button class="header-action" id="notificationBtn">
                    <i class="bi bi-bell"></i>
                    <?php if ($headerNotificationCount > 0): ?>
                        <span class="notification-badge"><?php echo $headerNotificationCount; ?></span>
                    <?php endif; ?>
                </button>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                        <span class="notification-count"><?php echo $headerNotificationCount; ?> new</span>
                    </div>
                    
                    <div class="notification-list">
                        <?php if (!empty($headerNotifications)): ?>
                            <?php foreach ($headerNotifications as $notif): ?>
                                <a href="<?php echo $notif['link']; ?>" class="notification-item">
                                    <div class="notification-icon <?php echo $notif['color']; ?>">
                                        <i class="bi bi-<?php echo $notif['icon']; ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                                        <div class="notification-description"><?php echo htmlspecialchars($notif['description']); ?></div>
                                        <div class="notification-time">
                                            <?php 
                                            $time = strtotime($notif['time']);
                                            $diff = time() - $time;
                                            if ($diff < 60) {
                                                echo 'Just now';
                                            } elseif ($diff < 3600) {
                                                echo floor($diff / 60) . ' min ago';
                                            } elseif ($diff < 86400) {
                                                echo floor($diff / 3600) . ' hrs ago';
                                            } else {
                                                echo date('M d, Y', $time);
                                            }
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
            
            <button class="header-action">
                <i class="bi bi-gear"></i>
            </button>
            <a href="../index.php" class="header-action" title="View Website">
                <i class="bi bi-eye"></i>
            </a>
        </div>
    </div>
</header>
