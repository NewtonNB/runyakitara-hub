<?php
$currentPage = 'news';
require_once 'config/database.php';
$db = getDBConnection();

$stmt = $db->query("SELECT * FROM articles WHERE deleted_at IS NULL ORDER BY created_at DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News &amp; Articles - Runyakitara Hub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/engagement.css">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <section class="page-hero">
        <div class="container">
            <div class="hero-content" data-aos="fade-up">
                <h1><i class="bi bi-newspaper"></i> News &amp; Articles</h1>
                <p>Stay informed with the latest cultural and language content</p>
            </div>
        </div>
    </section>

    <section class="articles-section">
        <div class="container">
            <?php if (empty($articles)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-newspaper"></i>
                    <h3>No Articles Yet</h3>
                    <p>Articles will be published soon. Check back later!</p>
                </div>
            <?php else: ?>
                <div class="articles-toolbar" data-aos="fade-up">
                    <p class="articles-count">Showing <span><?php echo count($articles); ?></span> article<?php echo count($articles) !== 1 ? 's' : ''; ?></p>
                </div>
                <div class="articles-grid" data-realtime="articles" data-aos="fade-up">
                    <?php foreach ($articles as $index => $article):
                        $displayDate = !empty($article['published_date'])
                            ? date('M d, Y', strtotime($article['published_date']))
                            : date('M d, Y', strtotime($article['created_at']));
                        $category = strtolower($article['category'] ?? 'news');
                    ?>
                        <a href="article.php?id=<?php echo (int)$article['id']; ?>" class="article-card" data-id="<?php echo (int)$article['id']; ?>" data-aos="fade-up" data-aos-delay="<?php echo ($index % 6) * 80; ?>" style="text-decoration:none;color:inherit;">
                            <div class="article-card-body">
                                <div class="article-header">
                                    <span class="category-badge category-<?php echo htmlspecialchars($category); ?>">
                                        <?php echo htmlspecialchars(ucfirst($article['category'] ?? 'News')); ?>
                                    </span>
                                    <span class="article-date">
                                        <i class="bi bi-calendar3"></i>
                                        <?php echo $displayDate; ?>
                                    </span>
                                </div>
                                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                <div class="article-author">
                                    <i class="bi bi-person-circle"></i>
                                    <?php echo htmlspecialchars($article['author'] ?? 'Unknown'); ?>
                                </div>
                                <?php if (!empty($article['excerpt'])): ?>
                                    <p class="article-excerpt"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="article-card-footer">
                                <span class="article-read-more">
                                    Read More <i class="bi bi-arrow-right"></i>
                                </span>
                                <div data-engagement data-eng-type="article" data-eng-id="<?php echo (int)$article['id']; ?>"></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script src="js/engagement.js"></script>
    <script src="js/realtime.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>
