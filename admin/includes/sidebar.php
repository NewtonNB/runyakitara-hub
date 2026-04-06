<aside class="admin-sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-logo">
            <img src="../images/hero/logo.jpeg" alt="Runyakitara Hub Logo" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.4);">
            <span>Runyakitara Hub</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="analytics.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i>
                <span>Analytics</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Content</div>
            <a href="lessons-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'lessons-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-book"></i>
                <span>Lessons</span>
            </a>
            <a href="dictionary-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dictionary-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-journal-text"></i>
                <span>Dictionary</span>
            </a>
            <a href="proverbs-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'proverbs-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-chat-quote"></i>
                <span>Proverbs</span>
            </a>
            <a href="grammar-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'grammar-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-pencil-square"></i>
                <span>Grammar</span>
            </a>
            <a href="articles-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'articles-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-newspaper"></i>
                <span>Articles</span>
            </a>
            <a href="media-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'media-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-play-circle"></i>
                <span>Media</span>
            </a>
            <a href="translations-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'translations-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-arrow-left-right"></i>
                <span>Translations</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Communication</div>
            <a href="messages-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'messages-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-envelope"></i>
                <span>Messages</span>
                <?php
                if (isset($stats) && $stats['contact_messages'] > 0):
                ?>
                    <span class="nav-badge"><?php echo $stats['contact_messages']; ?></span>
                <?php endif; ?>
            </a>
            <a href="comments-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'comments-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-chat-dots"></i>
                <span>Comments</span>
                <?php
                try {
                    $__db = getDBConnection();
                    $__pendingComments = (int)$__db->query("SELECT COUNT(*) FROM comments WHERE status != 'approved'")->fetchColumn();
                    closeDBConnection($__db);
                    if ($__pendingComments > 0): ?>
                        <span class="nav-badge"><?php echo $__pendingComments; ?></span>
                    <?php endif;
                } catch (Exception $__e) {}
                ?>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">System</div>
            <a href="users-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'users-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
            <?php
            // Show Roles link only to super admins
            if (isset($_SESSION['user_roles']) && in_array('super_admin', $_SESSION['user_roles'])):
            ?>
            <a href="roles-manage.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'roles-manage.php' ? 'active' : ''; ?>">
                <i class="bi bi-shield-check"></i>
                <span>Roles & Permissions</span>
            </a>
            <?php endif; ?>
            <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </div>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
                <div class="user-role"><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></div>
            </div>
        </div>
        <a href="logout.php" class="nav-item" style="margin-top: 10px;">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
