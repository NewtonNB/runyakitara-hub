<?php
$currentPage = 'dictionary';
require_once 'config/database.php';
$db = getDBConnection();

// Fetch dictionary entries
$stmt = $db->query("SELECT * FROM dictionary ORDER BY word_runyakitara ASC");
$words = $stmt->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dictionary - Runyakitara Hub</title>
    
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
                <h1><i class="bi bi-journal-text"></i> Dictionary</h1>
                <p>Comprehensive Runyakitara-English dictionary</p>
            </div>
        </div>
    </section>
    
    <section class="dictionary-section">
        <div class="container">
            <div class="search-box" data-aos="fade-up">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Search for words...">
            </div>
            
            <?php if (empty($words)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-book-half"></i>
                    <h3>No Words Available</h3>
                    <p>Dictionary entries will be added soon!</p>
                </div>
            <?php else: ?>
                <div class="dictionary-grid" id="dictionaryGrid">
                    <?php foreach ($words as $index => $word): ?>
                        <div class="word-card" data-aos="fade-up" data-aos-delay="<?php echo ($index % 12) * 50; ?>">
                            <div class="word-header">
                                <h3><?php echo htmlspecialchars($word['word_runyakitara']); ?></h3>
                                <?php if (!empty($word['category'])): ?>
                                    <span class="category-badge"><?php echo htmlspecialchars($word['category']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="word-translation">
                                <?php echo htmlspecialchars($word['word_english']); ?>
                            </div>
                            <?php if (!empty($word['pronunciation'])): ?>
                                <div class="word-pronunciation">
                                    <i class="bi bi-volume-up"></i>
                                    <?php echo htmlspecialchars($word['pronunciation']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($word['example_sentence'])): ?>
                                <div class="word-example">
                                    <i class="bi bi-chat-left-quote"></i>
                                    <?php echo htmlspecialchars($word['example_sentence']); ?>
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
        
        // Search functionality
        document.getElementById('searchInput')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.word-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
