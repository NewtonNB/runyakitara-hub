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
    <link rel="stylesheet" href="css/navbar.css">
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
                <div class="articles-toolbar" data-aos="fade-up">
                    <p class="articles-count"><span><?php echo count($articles); ?></span> articles published</p>
                </div>
                <div class="articles-grid">
                    <?php foreach ($articles as $index => $article):
                        $content = $article['excerpt'] ?? $article['content'] ?? '';
                        $wordCount = str_word_count(strip_tags($content));
                        $readTime = max(1, ceil($wordCount / 200));
                    ?>
                        <article class="article-card" data-aos="fade-up" data-aos-delay="<?php echo ($index % 6) * 80; ?>">
                            <div class="article-card-body">
                                <div class="article-header">
                                    <?php if (!empty($article['category'])): ?>
                                        <span class="category-badge category-<?php echo strtolower(htmlspecialchars($article['category'])); ?>">
                                            <?php echo htmlspecialchars($article['category']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span></span>
                                    <?php endif; ?>
                                    <span class="article-date">
                                        <i class="bi bi-calendar3"></i>
                                        <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                    </span>
                                </div>

                                <h3><?php echo htmlspecialchars($article['title']); ?></h3>

                                <?php if (!empty($article['author'])): ?>
                                    <div class="article-author">
                                        <i class="bi bi-person-circle"></i>
                                        By <?php echo htmlspecialchars($article['author']); ?>
                                    </div>
                                <?php endif; ?>

                                <p class="article-excerpt">
                                    <?php echo htmlspecialchars(substr(strip_tags($content), 0, 180)); ?>...
                                </p>
                            </div>

                            <div class="article-card-footer">
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="article-read-more">
                                    Read More <i class="bi bi-arrow-right"></i>
                                </a>
                                <span class="article-reading-time">
                                    <i class="bi bi-clock"></i> <?php echo $readTime; ?> min read
                                </span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>

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
