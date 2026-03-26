<?php
$currentPage = 'translations';
require_once 'config/database.php';
$db = getDBConnection();

$stmt = $db->query("SELECT * FROM translations ORDER BY created_at DESC");
$translations = $stmt->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translations - Runyakitara Hub</title>
    
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
                <h1><i class="bi bi-translate"></i> Translations</h1>
                <p>Cultural texts and their English translations</p>
            </div>
        </div>
    </section>
    
    <section class="translations-section">
        <div class="container">
            <?php if (empty($translations)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-file-text"></i>
                    <h3>No Translations Available</h3>
                    <p>Translations will be added soon!</p>
                </div>
            <?php else: ?>
                <div class="translations-grid">
                    <?php foreach ($translations as $index => $translation): ?>
                        <div class="translation-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="translation-header">
                                <h3><?php echo htmlspecialchars($translation['title']); ?></h3>
                                <span class="type-badge type-<?php echo strtolower($translation['type']); ?>">
                                    <?php echo ucfirst($translation['type']); ?>
                                </span>
                            </div>
                            <div class="translation-content">
                                <div class="translation-original">
                                    <strong>Runyakitara:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($translation['original_text'])); ?></p>
                                </div>
                                <div class="translation-divider">
                                    <i class="bi bi-arrow-down-up"></i>
                                </div>
                                <div class="translation-translated">
                                    <strong>English:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($translation['translated_text'])); ?></p>
                                </div>
                            </div>
                            <?php if (!empty($translation['cultural_context'])): ?>
                                <div class="translation-context">
                                    <i class="bi bi-info-circle"></i>
                                    <?php echo htmlspecialchars($translation['cultural_context']); ?>
                                </div>
                            <?php endif; ?>
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
