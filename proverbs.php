<?php
$currentPage = 'proverbs';
require_once 'config/database.php';
$db = getDBConnection();

$stmt = $db->query("SELECT * FROM proverbs ORDER BY created_at DESC");
$proverbs = $stmt->fetchAll(PDO::FETCH_ASSOC);

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proverbs - Runyakitara Hub</title>
    
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
                <h1><i class="bi bi-chat-quote"></i> Proverbs</h1>
                <p>Traditional wisdom and sayings</p>
            </div>
        </div>
    </section>
    
    <section class="proverbs-section">
        <div class="container">
            <?php if (empty($proverbs)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-chat-quote-fill"></i>
                    <h3>No Proverbs Available</h3>
                    <p>Proverbs will be added soon!</p>
                </div>
            <?php else: ?>
                <div class="proverbs-grid">
                    <?php foreach ($proverbs as $index => $proverb): ?>
                        <div class="proverb-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="proverb-icon">
                                <i class="bi bi-quote"></i>
                            </div>
                            <div class="proverb-text">
                                <?php echo htmlspecialchars($proverb['proverb'] ?? $proverb['proverb_text'] ?? ''); ?>
                            </div>
                            <div class="proverb-translation">
                                <strong>Translation:</strong> <?php echo htmlspecialchars($proverb['translation'] ?? ''); ?>
                            </div>
                            <div class="proverb-meaning">
                                <strong>Meaning:</strong> <?php echo htmlspecialchars($proverb['meaning'] ?? ''); ?>
                            </div>
                            <div data-engagement data-eng-type="proverb" data-eng-id="<?php echo $proverb['id']; ?>"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script src="js/engagement.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>
