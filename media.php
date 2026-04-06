<?php
$currentPage = 'media';
require_once 'config/database.php';
$db = getDBConnection();

$stmt = $db->query("SELECT * FROM media WHERE deleted_at IS NULL ORDER BY created_at DESC");
$mediaItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library - Runyakitara Hub</title>

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
                <h1><i class="bi bi-collection-play"></i> Media Library</h1>
                <p>Explore audio, video, and image resources for Runyakitara languages</p>
            </div>
        </div>
    </section>

    <section class="media-section">
        <div class="container">
            <?php if (empty($mediaItems)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-collection-play"></i>
                    <h3>No Media Yet</h3>
                    <p>Media files will be added soon. Check back later!</p>
                </div>
            <?php else: ?>
                <div class="media-filter-bar" data-aos="fade-up">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="audio">Audio</button>
                    <button class="filter-btn" data-filter="video">Video</button>
                    <button class="filter-btn" data-filter="image">Image</button>
                </div>
                <div class="media-grid" data-aos="fade-up">
                    <?php foreach ($mediaItems as $index => $item):
                        $type = strtolower($item['type'] ?? 'audio');
                        $typeIcon = $type === 'video' ? 'bi-camera-video' : ($type === 'image' ? 'bi-image' : 'bi-music-note-beamed');
                        $typeBadgeClass = $type === 'video' ? 'type-video' : ($type === 'image' ? 'type-image' : 'type-audio');
                    ?>
                        <div class="media-card" data-type="<?php echo htmlspecialchars($type); ?>" data-aos="fade-up" data-aos-delay="<?php echo ($index % 6) * 80; ?>">
                            <span class="media-type-badge <?php echo $typeBadgeClass; ?>">
                                <?php echo htmlspecialchars(ucfirst($type)); ?>
                            </span>

                            <div class="media-icon">
                                <i class="bi <?php echo $typeIcon; ?>"></i>
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

                            <div class="media-player" style="margin-top:16px;">
                                <?php if ($type === 'audio'): ?>
                                    <audio controls style="width:100%;">
                                        <source src="<?php echo htmlspecialchars($item['file_path']); ?>">
                                        Your browser does not support the audio element.
                                    </audio>
                                <?php elseif ($type === 'video'): ?>
                                    <video controls style="width:100%;border-radius:10px;">
                                        <source src="<?php echo htmlspecialchars($item['file_path']); ?>">
                                        Your browser does not support the video element.
                                    </video>
                                <?php elseif ($type === 'image'): ?>
                                    <img src="<?php echo htmlspecialchars($item['file_path']); ?>"
                                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                                         style="width:100%;border-radius:10px;object-fit:cover;max-height:200px;">
                                <?php endif; ?>
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

        // Filter bar logic
        const filterBtns = document.querySelectorAll('.filter-btn');
        const mediaCards = document.querySelectorAll('.media-card');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;
                mediaCards.forEach(card => {
                    if (filter === 'all' || card.dataset.type === filter) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
