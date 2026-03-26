<?php
$currentPage = 'media';
require_once 'config/database.php';
$db = getDBConnection();

$stmt = $db->query("SELECT * FROM media ORDER BY created_at DESC");
$mediaItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media - Runyakitara Hub</title>
    
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
                <h1><i class="bi bi-play-circle"></i> Media</h1>
                <p>Audio and video resources for learning</p>
            </div>
        </div>
    </section>
    
    <section class="media-section">
        <div class="container">
            <?php if (empty($mediaItems)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-film"></i>
                    <h3>No Media Available</h3>
                    <p>Media resources will be added soon!</p>
                </div>
            <?php else: ?>
                <div class="media-grid">
                    <?php foreach ($mediaItems as $index => $item): ?>
                        <div class="media-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="media-icon">
                                <i class="bi bi-<?php echo $item['type'] === 'audio' ? 'music-note-beamed' : 'play-btn'; ?>"></i>
                            </div>
                            <div class="media-type-badge type-<?php echo $item['type']; ?>">
                                <?php echo ucfirst($item['type']); ?>
                            </div>
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <?php if (!empty($item['description'])): ?>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['category'])): ?>
                                <div class="media-category">
                                    <i class="bi bi-tag"></i>
                                    <?php echo htmlspecialchars($item['category']); ?>
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
