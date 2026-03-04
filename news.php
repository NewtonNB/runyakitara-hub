<?php
$currentPage = 'news';
require_once 'config/database.php';
$db = getDBConnection();

$stmt = $db->query("SELECT * FROM articles ORDER BY created_at DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Articles - Runyakitara Hub</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <section class="page-hero">
        <div class="container">
            <div class="hero-content" data-aos="fade-up">
                <h1><i class="bi bi-newspaper"></i> News & Articles</h1>
                <p>Latest updates and cultural insights</p>
            </div>
        </div>
    </section>
    
    <section class="articles-section">
        <div class="container">
            <?php if (empty($articles)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-newspaper"></i>
                    <h3>No Articles Available</h3>
                    <p>Articles will be published soon!</p>
                </div>
            <?php else: ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $index => $article): ?>
                        <article class="article-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="article-header">
                                <?php if (!empty($article['category'])): ?>
                                    <span class="category-badge category-<?php echo strtolower($article['category']); ?>">
                                        <?php echo ucfirst($article['category']); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="article-date">
                                    <i class="bi bi-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                </span>
                            </div>
                            <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                            <?php if (!empty($article['author'])): ?>
                                <div class="article-author">
                                    <i class="bi bi-person"></i>
                                    By <?php echo htmlspecialchars($article['author']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="article-content">
                                <?php 
                                $content = $article['excerpt'] ?? $article['content'];
                                echo nl2br(htmlspecialchars(substr($content, 0, 200))) . '...'; 
                                ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
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
