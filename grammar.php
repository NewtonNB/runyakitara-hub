<?php
$currentPage = 'grammar';
require_once 'config/database.php';
$db = getDBConnection();

$stmt = $db->query("SELECT * FROM grammar_topics ORDER BY created_at ASC");
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grammar - Runyakitara Hub</title>
    
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
                <h1><i class="bi bi-pencil-square"></i> Grammar</h1>
                <p>Master the rules and patterns of Runyakitara</p>
            </div>
        </div>
    </section>
    
    <section class="grammar-section">
        <div class="container">
            <?php if (empty($topics)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-book"></i>
                    <h3>No Grammar Topics Available</h3>
                    <p>Grammar guides will be added soon!</p>
                </div>
            <?php else: ?>
                <div class="grammar-grid">
                    <?php foreach ($topics as $index => $topic): ?>
                        <div class="grammar-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="grammar-icon">
                                <i class="bi bi-book-half"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($topic['title']); ?></h3>
                            <div class="grammar-content">
                                <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
                            </div>
                            <?php if (!empty($topic['examples'])): ?>
                                <div class="grammar-examples">
                                    <strong><i class="bi bi-lightbulb"></i> Examples:</strong>
                                    <div><?php echo nl2br(htmlspecialchars($topic['examples'])); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
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
