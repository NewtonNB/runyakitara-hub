<?php
http_response_code(404);
$currentPage = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Runyakitara Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .error-page {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 20px;
            background: #f8fafc;
        }
        .error-inner { max-width: 560px; }
        .error-code {
            font-size: 120px;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
        }
        .error-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .error-desc {
            font-size: 16px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 36px;
        }
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(102,126,234,0.35);
        }
        .btn-home:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(102,126,234,0.45); color: white; }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .btn-back:hover { background: #667eea; color: white; }
        .error-links {
            margin-top: 40px;
            padding-top: 28px;
            border-top: 1px solid #e2e8f0;
        }
        .error-links p { font-size: 14px; color: #94a3b8; margin-bottom: 14px; }
        .error-links-grid {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .error-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            text-decoration: none;
            transition: all 0.2s;
        }
        .error-link:hover { border-color: #667eea; color: #667eea; background: rgba(102,126,234,0.05); }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <div class="error-page">
        <div class="error-inner">
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-desc">
                The page you're looking for doesn't exist or may have been moved.
                Let's get you back on track.
            </p>
            <div class="error-actions">
                <a href="index.php" class="btn-home">
                    <i class="bi bi-house-door"></i> Go Home
                </a>
                <a href="javascript:history.back()" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Go Back
                </a>
            </div>
            <div class="error-links">
                <p>Or explore these pages:</p>
                <div class="error-links-grid">
                    <a href="lessons.php" class="error-link"><i class="bi bi-book"></i> Lessons</a>
                    <a href="dictionary.php" class="error-link"><i class="bi bi-journal-text"></i> Dictionary</a>
                    <a href="proverbs.php" class="error-link"><i class="bi bi-chat-quote"></i> Proverbs</a>
                    <a href="news.php" class="error-link"><i class="bi bi-newspaper"></i> News</a>
                    <a href="contact.php" class="error-link"><i class="bi bi-envelope"></i> Contact</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>
