<?php
$currentPage = 'lessons';
require_once 'config/database.php';
$db = getDBConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: lessons.php'); exit; }

$stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$lesson) { header('Location: lessons.php'); exit; }

$prev = $db->prepare("SELECT id, title, lesson_order FROM lessons WHERE lesson_order < ? ORDER BY lesson_order DESC LIMIT 1");
$prev->execute([$lesson['lesson_order']]);
$prevLesson = $prev->fetch(PDO::FETCH_ASSOC);

$next = $db->prepare("SELECT id, title, lesson_order FROM lessons WHERE lesson_order > ? ORDER BY lesson_order ASC LIMIT 1");
$next->execute([$lesson['lesson_order']]);
$nextLesson = $next->fetch(PDO::FETCH_ASSOC);

$all = $db->query("SELECT id, title, lesson_order, level FROM lessons ORDER BY lesson_order ASC");
$allLessons = $all->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);

$wordCount = str_word_count(strip_tags($lesson['content'] ?? ''));
$readTime  = max(1, ceil($wordCount / 150));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - Runyakitara Hub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/lesson.css">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <!-- Hero -->
    <section class="lesson-hero">
        <div class="container">
            <div class="lesson-hero-inner" data-aos="fade-up">
                <a href="lessons.php" class="lesson-back-link">
                    <i class="bi bi-arrow-left"></i> Back to Lessons
                </a>
                <div class="lesson-hero-badges">
                    <span class="lesson-number-badge">Lesson <?php echo $lesson['lesson_order']; ?></span>
                    <span class="level-badge level-<?php echo strtolower($lesson['level']); ?>">
                        <?php echo ucfirst($lesson['level']); ?>
                    </span>
                </div>
                <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
                <?php if (!empty($lesson['description'])): ?>
                    <p class="lesson-hero-desc"><?php echo htmlspecialchars($lesson['description']); ?></p>
                <?php endif; ?>
                <div class="lesson-hero-meta">
                    <span><i class="bi bi-clock"></i> <?php echo $readTime; ?> min read</span>
                    <span><i class="bi bi-file-text"></i> <?php echo number_format($wordCount); ?> words</span>
                    <span><i class="bi bi-bar-chart-line"></i> <?php echo ucfirst($lesson['level']); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Body -->
    <div class="lesson-page-body">
        <div class="container">
            <div class="lesson-page-grid">

                <!-- Main -->
                <main class="lesson-main" data-aos="fade-up">
                    <div class="lesson-content-card">
                        <div class="lesson-text">
                            <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
                        </div>

                        <!-- Prev / Next -->
                        <div class="lesson-nav">
                            <?php if ($prevLesson): ?>
                                <a href="lesson.php?id=<?php echo $prevLesson['id']; ?>" class="lesson-nav-btn">
                                    <i class="bi bi-arrow-left"></i>
                                    <div>
                                        <span>Previous</span>
                                        <strong>Lesson <?php echo $prevLesson['lesson_order']; ?>: <?php echo htmlspecialchars($prevLesson['title']); ?></strong>
                                    </div>
                                </a>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>

                            <?php if ($nextLesson): ?>
                                <a href="lesson.php?id=<?php echo $nextLesson['id']; ?>" class="lesson-nav-btn lesson-nav-next">
                                    <div>
                                        <span>Next</span>
                                        <strong>Lesson <?php echo $nextLesson['lesson_order']; ?>: <?php echo htmlspecialchars($nextLesson['title']); ?></strong>
                                    </div>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <a href="lessons.php" class="lesson-all-btn">
                            <i class="bi bi-grid"></i> All Lessons
                        </a>
                    </div>
                </main>

                <!-- Sidebar -->
                <aside class="lesson-sidebar">
                    <div class="lesson-sidebar-card">
                        <div class="lesson-sidebar-header">
                            <i class="bi bi-list-ol"></i>
                            <h4>All Lessons</h4>
                        </div>
                        <div class="lesson-list">
                            <?php foreach ($allLessons as $l): ?>
                                <a href="lesson.php?id=<?php echo $l['id']; ?>"
                                   class="lesson-list-item<?php echo $l['id'] == $id ? ' active' : ''; ?>">
                                    <span class="lesson-list-num"><?php echo $l['lesson_order']; ?></span>
                                    <span class="lesson-list-title"><?php echo htmlspecialchars($l['title']); ?></span>
                                    <span class="lesson-list-level level-badge level-<?php echo strtolower($l['level']); ?>">
                                        <?php echo ucfirst($l['level']); ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script>
        AOS.init({ duration: 800, once: true, offset: 50 });
    </script>
</body>
</html>
