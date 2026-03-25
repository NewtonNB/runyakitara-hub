<link rel="stylesheet" href="css/navbar.css">
<nav class="navbar-modern">
    <div class="container">
        <div class="navbar-brand">
            <div class="logo-wrapper">
                <i class="bi bi-translate logo-icon"></i>
                <div class="logo-text">
                    <span class="logo-main">Runyakitara Hub</span>
                    <span class="logo-tagline">Language & Culture</span>
                </div>
            </div>
        </div>
        
        <button class="mobile-toggle" aria-label="Toggle menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
        
        <div class="nav-menu">
            <ul class="nav-links-modern">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo ($currentPage == 'home') ? 'active' : ''; ?>">
                        <i class="bi bi-house-door"></i>
                        <span>Home</span>
                    </a>
                </li>
                
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link <?php echo in_array($currentPage, ['lessons', 'grammar', 'dictionary']) ? 'active' : ''; ?>">
                        <i class="bi bi-book"></i>
                        <span>Learn</span>
                        <i class="bi bi-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="lessons.php" class="dropdown-item <?php echo ($currentPage == 'lessons') ? 'active' : ''; ?>">
                                <i class="bi bi-journal-bookmark"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Lessons</span>
                                    <span class="dropdown-desc">Structured courses</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="grammar.php" class="dropdown-item <?php echo ($currentPage == 'grammar') ? 'active' : ''; ?>">
                                <i class="bi bi-pencil-square"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Grammar</span>
                                    <span class="dropdown-desc">Rules & patterns</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="dictionary.php" class="dropdown-item <?php echo ($currentPage == 'dictionary') ? 'active' : ''; ?>">
                                <i class="bi bi-journal-text"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Dictionary</span>
                                    <span class="dropdown-desc">1000+ words</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link <?php echo in_array($currentPage, ['proverbs', 'news', 'media', 'translations']) ? 'active' : ''; ?>">
                        <i class="bi bi-globe"></i>
                        <span>Culture</span>
                        <i class="bi bi-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="proverbs.php" class="dropdown-item <?php echo ($currentPage == 'proverbs') ? 'active' : ''; ?>">
                                <i class="bi bi-chat-quote"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Proverbs</span>
                                    <span class="dropdown-desc">Traditional wisdom</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="news.php" class="dropdown-item <?php echo ($currentPage == 'news') ? 'active' : ''; ?>">
                                <i class="bi bi-newspaper"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">News</span>
                                    <span class="dropdown-desc">Latest updates</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="media.php" class="dropdown-item <?php echo ($currentPage == 'media') ? 'active' : ''; ?>">
                                <i class="bi bi-play-circle"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Media</span>
                                    <span class="dropdown-desc">Audio & video</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="translations.php" class="dropdown-item <?php echo ($currentPage == 'translations') ? 'active' : ''; ?>">
                                <i class="bi bi-translate"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Translations</span>
                                    <span class="dropdown-desc">Cultural texts</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a href="contact.php" class="nav-link <?php echo ($currentPage == 'contact') ? 'active' : ''; ?>">
                        <i class="bi bi-envelope"></i>
                        <span>Contact</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="admin/login.php" class="nav-link nav-link-cta">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Login</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
