<?php
$currentPage = 'news';
require_once 'config/database.php';
$db = getDBConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: news.php'); exit; }

$stmt = $db->prepare("SELECT * FROM articles WHERE id = ? AND (deleted_at IS NULL OR deleted_at = '')");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    // fallback without soft-delete column
    $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$article) { header('Location: news.php'); exit; }

// Related articles
$related = $db->prepare("SELECT id, title, author, created_at, category FROM articles WHERE id != ? ORDER BY created_at DESC LIMIT 3");
$related->execute([$id]);
$relatedArticles = $related->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);

$content   = $article['content'] ?? '';
$wordCount = str_word_count(strip_tags($content));
$readTime  = max(1, ceil($wordCount / 200));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Runyakitara Hub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/article.css">
    <link rel="stylesheet" href="css/engagement.css">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <!-- Article Hero -->
    <section class="article-hero">
        <div class="container">
            <div class="article-hero-inner" data-aos="fade-up">
                <a href="news.php" class="article-back">
                    <i class="bi bi-arrow-left"></i> Back to Articles
                </a>
                <?php if (!empty($article['category'])): ?>
                    <span class="category-badge category-<?php echo strtolower(htmlspecialchars($article['category'])); ?>">
                        <?php echo htmlspecialchars($article['category']); ?>
                    </span>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="article-meta">
                    <?php if (!empty($article['author'])): ?>
                        <span><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($article['author']); ?></span>
                    <?php endif; ?>
                    <span><i class="bi bi-calendar3"></i> <?php echo date('F d, Y', strtotime(!empty($article['published_date']) ? $article['published_date'] : $article['created_at'])); ?></span>
                    <span><i class="bi bi-clock"></i> <?php echo $readTime; ?> min read</span>
                    <span><i class="bi bi-book"></i> <?php echo number_format($wordCount); ?> words</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Article Body -->
    <section class="article-body-section">
        <div class="container">
            <div class="article-layout">

                <!-- Main Content -->
                <article class="article-content-main" data-aos="fade-up">
                    <?php if (!empty($article['excerpt'])): ?>
                        <p class="article-lead"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                    <?php endif; ?>

                    <div class="article-body">
                        <?php echo nl2br(htmlspecialchars($content)); ?>
                    </div>

                    <!-- Share -->
                    <div class="article-share">
                        <span>Share this article:</span>
                        <div class="share-buttons">
                            <a href="https://wa.me/?text=<?php echo urlencode($article['title'] . ' - ' . 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn share-whatsapp">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($article['title']); ?>&url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn share-twitter">
                                <i class="bi bi-twitter"></i> Twitter
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn share-facebook">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                            <a href="https://t.me/share/url?url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($article['title']); ?>" target="_blank" class="share-btn share-telegram">
                                <i class="bi bi-telegram"></i> Telegram
                            </a>
                            <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>this.innerHTML='<i class=\'bi bi-check-lg\'></i> Copied!')" class="share-btn share-copy">
                                <i class="bi bi-link-45deg"></i> Copy Link
                            </button>
                        </div>
                    </div>

                    <a href="news.php" class="article-back-btn">
                        <i class="bi bi-arrow-left"></i> Back to All Articles
                    </a>

                    <!-- Engagement -->
                    <div data-engagement data-eng-type="article" data-eng-id="<?php echo (int)$article['id']; ?>"></div>
                </article>

                <!-- Sidebar -->
                <aside class="article-sidebar">
                    <?php if (!empty($relatedArticles)): ?>
                    <div class="sidebar-card">
                        <h4><i class="bi bi-newspaper"></i> More Articles</h4>
                        <div class="related-list">
                            <?php foreach ($relatedArticles as $rel): ?>
                                <a href="article.php?id=<?php echo $rel['id']; ?>" class="related-item">
                                    <div class="related-item-inner">
                                        <?php if (!empty($rel['category'])): ?>
                                            <span class="category-badge category-<?php echo strtolower(htmlspecialchars($rel['category'])); ?>" style="font-size:10px;padding:3px 10px;">
                                                <?php echo htmlspecialchars($rel['category']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <p><?php echo htmlspecialchars($rel['title']); ?></p>
                                        <span class="related-date">
                                            <i class="bi bi-calendar3"></i>
                                            <?php echo date('M d, Y', strtotime($rel['created_at'])); ?>
                                        </span>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="sidebar-card sidebar-cta">
                        <i class="bi bi-translate"></i>
                        <h4>Start Learning</h4>
                        <p>Explore lessons, dictionary, and more.</p>
                        <a href="lessons.php" class="sidebar-cta-btn">Go to Lessons</a>
                    </div>
                </aside>

            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script src="js/engagement.js"></script>
    <script>
        AOS.init({ duration: 800, once: true, offset: 100 });
    </script>
</body>
</html>
