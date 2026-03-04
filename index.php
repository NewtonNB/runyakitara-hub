<?php
$currentPage = 'home';

// Check if database exists
$dbPath = __DIR__ . '/data/runyakitara.db';
if (!file_exists($dbPath)) {
    header('Location: init-database.php');
    exit;
}

require_once 'config/database.php';
$db = getDBConnection();

// Get statistics
$stats = [
    'lessons' => $db->query("SELECT COUNT(*) FROM lessons")->fetchColumn(),
    'words' => $db->query("SELECT COUNT(*) FROM dictionary")->fetchColumn(),
    'proverbs' => $db->query("SELECT COUNT(*) FROM proverbs")->fetchColumn(),
    'articles' => $db->query("SELECT COUNT(*) FROM articles")->fetchColumn()
];

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Runyakitara Hub - Learn Runyankore, Rukiga, Runyoro & Rutooro</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-modern">
        <div class="hero-background">
            <div class="hero-gradient"></div>
            <div class="hero-pattern"></div>
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
        </div>
        
        <div class="container">
            <div class="hero-content-modern" data-aos="fade-up">
                <div class="hero-badge">
                    <i class="bi bi-stars"></i>
                    <span>Your Gateway to Runyakitara Languages</span>
                </div>
                
                <h1 class="hero-title-modern">
                    Master <span class="gradient-text">Runyakitara</span><br>
                    Languages with Confidence
                </h1>
                
                <p class="hero-description">
                    Learn Runyankore, Rukiga, Runyoro, and Rutooro through interactive lessons, 
                    comprehensive dictionary, cultural insights, and authentic proverbs.
                </p>
                
                <div class="hero-actions">
                    <a href="lessons.php" class="btn-hero btn-hero-primary">
                        <i class="bi bi-play-circle-fill"></i>
                        <span>Start Learning Free</span>
                    </a>
                    <a href="dictionary.php" class="btn-hero btn-hero-secondary">
                        <i class="bi bi-book"></i>
                        <span>Explore Dictionary</span>
                    </a>
                </div>
                
                <div class="hero-stats-modern">
                    <div class="hero-stat-item">
                        <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                        <div class="stat-info">
                            <span class="stat-number">10K+</span>
                            <span class="stat-label">Active Learners</span>
                        </div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
                        <div class="stat-info">
                            <span class="stat-number">4.9/5</span>
                            <span class="stat-label">User Rating</span>
                        </div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="stat-icon"><i class="bi bi-trophy-fill"></i></div>
                        <div class="stat-info">
                            <span class="stat-number">50K+</span>
                            <span class="stat-label">Lessons Completed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="scroll-indicator">
            <i class="bi bi-chevron-down"></i>
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="stats-modern">
        <div class="container">
            <div class="stats-grid-modern">
                <div class="stat-card-modern" data-aos="fade-up" data-aos-delay="0">
                    <div class="stat-icon-wrapper">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number-modern"><?php echo $stats['lessons']; ?>+</h3>
                        <p class="stat-label-modern">Interactive Lessons</p>
                        <span class="stat-badge">Beginner to Advanced</span>
                    </div>
                </div>
                
                <div class="stat-card-modern" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon-wrapper">
                        <i class="bi bi-book-fill"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number-modern"><?php echo $stats['words']; ?>+</h3>
                        <p class="stat-label-modern">Dictionary Words</p>
                        <span class="stat-badge">With Audio</span>
                    </div>
                </div>
                
                <div class="stat-card-modern" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon-wrapper">
                        <i class="bi bi-chat-quote-fill"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number-modern"><?php echo $stats['proverbs']; ?>+</h3>
                        <p class="stat-label-modern">Traditional Proverbs</p>
                        <span class="stat-badge">Cultural Wisdom</span>
                    </div>
                </div>
                
                <div class="stat-card-modern" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-icon-wrapper">
                        <i class="bi bi-newspaper"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number-modern"><?php echo $stats['articles']; ?>+</h3>
                        <p class="stat-label-modern">Cultural Articles</p>
                        <span class="stat-badge">Updated Weekly</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-modern">
        <div class="container">
            <div class="section-header-modern" data-aos="fade-up">
                <span class="section-badge-modern">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Why Choose Us
                </span>
                <h2 class="section-title-modern">Everything You Need to Master Runyakitara</h2>
                <p class="section-description-modern">
                    Comprehensive learning tools designed to help you speak, read, and understand 
                    Runyakitara languages fluently.
                </p>
            </div>
            
            <div class="features-grid-modern">
                <div class="feature-card-modern" data-aos="fade-up" data-aos-delay="0">
                    <div class="feature-icon-modern">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <h3>Structured Learning Path</h3>
                    <p>Follow our carefully designed curriculum from basics to advanced fluency with step-by-step guidance.</p>
                    <a href="lessons.php" class="feature-link">
                        <span>Start Learning</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                
                <div class="feature-card-modern" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon-modern">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <h3>Rich Dictionary</h3>
                    <p>Access thousands of words with pronunciations, examples, and usage contexts in all four languages.</p>
                    <a href="dictionary.php" class="feature-link">
                        <span>Browse Words</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                
                <div class="feature-card-modern" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon-modern">
                        <i class="bi bi-globe2"></i>
                    </div>
                    <h3>Cultural Immersion</h3>
                    <p>Learn through authentic proverbs, stories, and cultural contexts that bring the language to life.</p>
                    <a href="proverbs.php" class="feature-link">
                        <span>Explore Culture</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                
                <div class="feature-card-modern" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon-modern">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <h3>Grammar Mastery</h3>
                    <p>Clear explanations of grammar rules, sentence structures, and language patterns with examples.</p>
                    <a href="grammar.php" class="feature-link">
                        <span>Learn Grammar</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                
                <div class="feature-card-modern" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon-modern">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                    <h3>Audio & Video</h3>
                    <p>Listen to native speakers and watch video lessons to perfect your pronunciation and comprehension.</p>
                    <a href="media.php" class="feature-link">
                        <span>View Media</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                
                <div class="feature-card-modern" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon-modern">
                        <i class="bi bi-translate"></i>
                    </div>
                    <h3>Translation Tools</h3>
                    <p>Translate texts and learn how to express ideas naturally in Runyakitara languages.</p>
                    <a href="translations.php" class="feature-link">
                        <span>Try Translator</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Languages Section -->
    <section class="languages-modern">
        <div class="container">
            <div class="section-header-modern" data-aos="fade-up">
                <span class="section-badge-modern">
                    <i class="bi bi-translate"></i>
                    Four Languages, One Platform
                </span>
                <h2 class="section-title-modern">Learn All Runyakitara Languages</h2>
                <p class="section-description-modern">
                    Master the four closely related languages spoken across Western Uganda.
                </p>
            </div>
            
            <div class="languages-grid-modern">
                <div class="language-card-modern" data-aos="fade-up" data-aos-delay="0">
                    <div class="language-number">01</div>
                    <h3>Runyankore</h3>
                    <p>Spoken primarily in Ankole region, Runyankore is one of the most widely spoken Runyakitara languages.</p>
                    <div class="language-stats-modern">
                        <span><i class="bi bi-people-fill"></i> 2.3M+ Speakers</span>
                        <span><i class="bi bi-geo-alt-fill"></i> Ankole Region</span>
                    </div>
                </div>
                
                <div class="language-card-modern" data-aos="fade-up" data-aos-delay="100">
                    <div class="language-number">02</div>
                    <h3>Rukiga</h3>
                    <p>The language of Kigezi region, known for its rich cultural heritage and beautiful highlands.</p>
                    <div class="language-stats-modern">
                        <span><i class="bi bi-people-fill"></i> 2.0M+ Speakers</span>
                        <span><i class="bi bi-geo-alt-fill"></i> Kigezi Region</span>
                    </div>
                </div>
                
                <div class="language-card-modern" data-aos="fade-up" data-aos-delay="200">
                    <div class="language-number">03</div>
                    <h3>Runyoro</h3>
                    <p>Spoken in Bunyoro kingdom, one of the oldest kingdoms in Uganda with deep historical roots.</p>
                    <div class="language-stats-modern">
                        <span><i class="bi bi-people-fill"></i> 1.5M+ Speakers</span>
                        <span><i class="bi bi-geo-alt-fill"></i> Bunyoro Region</span>
                    </div>
                </div>
                
                <div class="language-card-modern" data-aos="fade-up" data-aos-delay="300">
                    <div class="language-number">04</div>
                    <h3>Rutooro</h3>
                    <p>The language of Tooro kingdom, closely related to Runyoro with unique cultural expressions.</p>
                    <div class="language-stats-modern">
                        <span><i class="bi bi-people-fill"></i> 1.4M+ Speakers</span>
                        <span><i class="bi bi-geo-alt-fill"></i> Tooro Region</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-modern">
        <div class="cta-background">
            <div class="cta-gradient"></div>
            <div class="cta-pattern"></div>
        </div>
        
        <div class="container">
            <div class="cta-content-modern" data-aos="fade-up">
                <div class="cta-icon">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                </div>
                <h2>Ready to Start Your Language Journey?</h2>
                <p>Join thousands of learners mastering Runyakitara languages. Start learning today for free!</p>
                <div class="cta-actions">
                    <a href="lessons.php" class="btn-cta btn-cta-primary">
                        <i class="bi bi-play-circle-fill"></i>
                        <span>Begin Learning Now</span>
                    </a>
                    <a href="contact.php" class="btn-cta btn-cta-secondary">
                        <i class="bi bi-envelope-fill"></i>
                        <span>Contact Us</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer-modern">
        <div class="container">
            <div class="footer-content-modern">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <i class="bi bi-translate"></i>
                        <span>Runyakitara Hub</span>
                    </div>
                    <p>Preserving language, celebrating culture, and connecting communities through education.</p>
                    <div class="social-links-modern">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-links-modern">
                    <div class="footer-column">
                        <h4>Learn</h4>
                        <ul>
                            <li><a href="lessons.php">Lessons</a></li>
                            <li><a href="dictionary.php">Dictionary</a></li>
                            <li><a href="grammar.php">Grammar</a></li>
                            <li><a href="proverbs.php">Proverbs</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Resources</h4>
                        <ul>
                            <li><a href="media.php">Media</a></li>
                            <li><a href="translations.php">Translations</a></li>
                            <li><a href="news.php">News</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Support</h4>
                        <ul>
                            <li><a href="contact.php">Contact</a></li>
                            <li><a href="#">Help Center</a></li>
                            <li><a href="admin/login.php">Admin Login</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom-modern">
                <p>&copy; <?php echo date('Y'); ?> Runyakitara Hub. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>
