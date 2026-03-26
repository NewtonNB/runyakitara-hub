<?php
$currentPage = 'lessons';
require_once 'config/database.php';
$db = getDBConnection();

// Fetch lessons from database
$stmt = $db->query("SELECT * FROM lessons ORDER BY lesson_order ASC");
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lessons - Runyakitara Hub</title>
    
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
                <h1><i class="bi bi-journal-bookmark"></i> Lessons</h1>
                <p>Structured courses to master Runyakitara languages</p>
            </div>
        </div>
    </section>
    
    <section class="lessons-section">
        <div class="container">
            <?php if (empty($lessons)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-journal-x"></i>
                    <h3>No Lessons Available</h3>
                    <p>Lessons will be added soon. Check back later!</p>
                </div>
            <?php else: ?>
                <div class="lessons-grid">
                    <?php foreach ($lessons as $index => $lesson):
                        $wordCount = str_word_count(strip_tags($lesson['content'] ?? ''));
                        $readTime  = max(1, ceil($wordCount / 150));
                    ?>
                        <div class="lesson-card" data-aos="fade-up" data-aos-delay="<?php echo ($index % 6) * 80; ?>">
                            <div class="lesson-header">
                                <div class="lesson-number">Lesson <?php echo $lesson['lesson_order']; ?></div>
                                <span class="level-badge level-<?php echo strtolower($lesson['level']); ?>">
                                    <?php echo ucfirst($lesson['level']); ?>
                                </span>
                            </div>
                            <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                            <p class="lesson-description"><?php echo htmlspecialchars(substr($lesson['description'] ?? '', 0, 120)); ?></p>
                            <div class="lesson-footer">
                                <span class="lesson-meta">
                                    <i class="bi bi-clock"></i> <?php echo $readTime; ?> min
                                </span>
                                <a href="lesson.php?id=<?php echo $lesson['id']; ?>" class="lesson-start-btn">
                                    Start Lesson <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
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
