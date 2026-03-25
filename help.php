<?php $currentPage = 'help'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - Runyakitara Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/pages.css">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <section class="page-hero">
        <div class="container">
            <div class="hero-content" data-aos="fade-up">
                <h1><i class="bi bi-question-circle"></i> Help Center</h1>
                <p>Find answers to common questions about Runyakitara Hub</p>
            </div>
        </div>
    </section>

    <section style="padding: 80px 0; background: #f8fafc;">
        <div class="container">

            <!-- Search -->
            <div class="search-box" data-aos="fade-up" style="margin-bottom: 60px;">
                <i class="bi bi-search"></i>
                <input type="text" id="faqSearch" placeholder="Search for answers...">
            </div>

            <div class="help-layout">

                <!-- Categories sidebar -->
                <aside class="help-sidebar" data-aos="fade-right">
                    <div class="help-cat-card">
                        <h4>Categories</h4>
                        <ul class="help-cat-list">
                            <li><a href="#getting-started" class="help-cat-link active"><i class="bi bi-rocket-takeoff"></i> Getting Started</a></li>
                            <li><a href="#lessons" class="help-cat-link"><i class="bi bi-journal-bookmark"></i> Lessons</a></li>
                            <li><a href="#dictionary" class="help-cat-link"><i class="bi bi-book"></i> Dictionary</a></li>
                            <li><a href="#account" class="help-cat-link"><i class="bi bi-person-circle"></i> Account</a></li>
                            <li><a href="#contact" class="help-cat-link"><i class="bi bi-envelope"></i> Contact & Support</a></li>
                        </ul>
                    </div>

                    <div class="help-contact-card">
                        <i class="bi bi-headset"></i>
                        <h4>Still need help?</h4>
                        <p>Our team is happy to assist you.</p>
                        <a href="contact.php" class="help-contact-btn">Contact Us</a>
                    </div>
                </aside>

                <!-- FAQ content -->
                <div class="help-content" data-aos="fade-up">

                    <!-- Getting Started -->
                    <div class="faq-section" id="getting-started">
                        <div class="faq-section-header">
                            <i class="bi bi-rocket-takeoff"></i>
                            <h2>Getting Started</h2>
                        </div>
                        <div class="faq-list">
                            <div class="faq-item">
                                <button class="faq-question">
                                    What is Runyakitara Hub?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Runyakitara Hub is a free online platform for learning the four Runyakitara languages — Runyankore, Rukiga, Runyoro, and Rutooro. It offers structured lessons, a comprehensive dictionary, grammar guides, cultural proverbs, and media resources.</p>
                                </div>
                            </div>
                            <div class="faq-item">
                                <button class="faq-question">
                                    Is it free to use?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Yes, Runyakitara Hub is completely free. All lessons, dictionary entries, grammar topics, and cultural content are available at no cost.</p>
                                </div>
                            </div>
                            <div class="faq-item">
                                <button class="faq-question">
                                    Do I need to create an account?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>No account is needed to browse lessons, the dictionary, or any public content. An account is only required for admin and content management purposes.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lessons -->
                    <div class="faq-section" id="lessons">
                        <div class="faq-section-header">
                            <i class="bi bi-journal-bookmark"></i>
                            <h2>Lessons</h2>
                        </div>
                        <div class="faq-list">
                            <div class="faq-item">
                                <button class="faq-question">
                                    Where do I start as a beginner?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Head to the <a href="lessons.php">Lessons</a> page and start from Lesson 1. Lessons are ordered from beginner to advanced, so just follow them in sequence.</p>
                                </div>
                            </div>
                            <div class="faq-item">
                                <button class="faq-question">
                                    Can I jump between lessons?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Yes. You can open any lesson directly from the lessons list or use the sidebar on the lesson detail page to jump to any lesson at any time.</p>
                                </div>
                            </div>
                            <div class="faq-item">
                                <button class="faq-question">
                                    How long does each lesson take?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Most lessons take between 5 and 20 minutes depending on the content length. Each lesson shows an estimated reading time at the top.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dictionary -->
                    <div class="faq-section" id="dictionary">
                        <div class="faq-section-header">
                            <i class="bi bi-book"></i>
                            <h2>Dictionary</h2>
                        </div>
                        <div class="faq-list">
                            <div class="faq-item">
                                <button class="faq-question">
                                    How do I search for a word?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Go to the <a href="dictionary.php">Dictionary</a> page and type in the search box. It searches both Runyakitara and English words in real time.</p>
                                </div>
                            </div>
                            <div class="faq-item">
                                <button class="faq-question">
                                    Can I suggest a word to be added?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Yes! Use the <a href="contact.php">Contact</a> page to send us a word suggestion and we'll review it for inclusion.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account -->
                    <div class="faq-section" id="account">
                        <div class="faq-section-header">
                            <i class="bi bi-person-circle"></i>
                            <h2>Account</h2>
                        </div>
                        <div class="faq-list">
                            <div class="faq-item">
                                <button class="faq-question">
                                    I forgot my admin password. What do I do?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Contact us via the <a href="contact.php">Contact</a> page with your username and we'll help you reset your password.</p>
                                </div>
                            </div>
                            <div class="faq-item">
                                <button class="faq-question">
                                    How do I become a content contributor?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Reach out to us through the <a href="contact.php">Contact</a> page expressing your interest. We'll get back to you with details on how to contribute lessons, articles, or dictionary entries.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div class="faq-section" id="contact">
                        <div class="faq-section-header">
                            <i class="bi bi-envelope"></i>
                            <h2>Contact & Support</h2>
                        </div>
                        <div class="faq-list">
                            <div class="faq-item">
                                <button class="faq-question">
                                    How do I contact the team?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Use our <a href="contact.php">Contact page</a> or email us directly at <a href="mailto:runyakitarahub22@gmail.com">runyakitarahub22@gmail.com</a>. We respond within 24–48 hours.</p>
                                </div>
                            </div>
                            <div class="faq-item">
                                <button class="faq-question">
                                    Where can I follow Runyakitara Hub on social media?
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="faq-answer">
                                    <p>Follow us on:</p>
                                    <ul style="margin-top:8px; padding-left:20px; line-height:2;">
                                        <li><a href="https://www.youtube.com/@RunyakitaraHub" target="_blank">YouTube — @RunyakitaraHub</a></li>
                                        <li><a href="https://tiktok.com/@runyakitaratothewolrd" target="_blank">TikTok — @runyakitaratothewolrd</a></li>
                                        <li><a href="https://whatsapp.com/channel/0029Vb7JLlXLY6d5H1Vltt0D" target="_blank">WhatsApp Channel</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script>
        AOS.init({ duration: 800, once: true, offset: 80 });

        // FAQ accordion
        document.querySelectorAll('.faq-question').forEach(btn => {
            btn.addEventListener('click', function() {
                const item = this.closest('.faq-item');
                const isOpen = item.classList.contains('open');
                document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
                if (!isOpen) item.classList.add('open');
            });
        });

        // Search filter
        document.getElementById('faqSearch').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.faq-item').forEach(item => {
                item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        // Smooth scroll for category links
        document.querySelectorAll('.help-cat-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.help-cat-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>

    <style>
        .help-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 40px;
            align-items: start;
        }

        /* Sidebar */
        .help-sidebar { position: sticky; top: 90px; display: flex; flex-direction: column; gap: 20px; }

        .help-cat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        }
        .help-cat-card h4 {
            font-size: 13px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 14px;
        }
        .help-cat-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 2px; }
        .help-cat-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: #4a5568;
            transition: all 0.2s ease;
        }
        .help-cat-link:hover, .help-cat-link.active {
            background: rgba(102,126,234,0.08);
            color: #667eea;
        }
        .help-cat-link i { font-size: 16px; }

        .help-contact-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
        }
        .help-contact-card i { font-size: 36px; color: rgba(255,255,255,0.9); margin-bottom: 10px; display: block; }
        .help-contact-card h4 { font-size: 16px; font-weight: 700; color: white; margin-bottom: 8px; }
        .help-contact-card p { font-size: 13px; color: rgba(255,255,255,0.82); margin-bottom: 16px; }
        .help-contact-btn {
            display: inline-block;
            padding: 10px 24px;
            background: white;
            color: #667eea;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .help-contact-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.15); }

        /* FAQ sections */
        .faq-section { margin-bottom: 48px; }

        .faq-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 2px solid #e2e8f0;
        }
        .faq-section-header i { font-size: 24px; color: #667eea; }
        .faq-section-header h2 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }

        .faq-list { display: flex; flex-direction: column; gap: 8px; }

        .faq-item {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            overflow: hidden;
            border: 2px solid transparent;
            transition: border-color 0.25s ease;
        }
        .faq-item.open { border-color: #667eea; }

        .faq-question {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            text-align: left;
            gap: 12px;
        }
        .faq-question i {
            font-size: 16px;
            color: #667eea;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }
        .faq-item.open .faq-question i { transform: rotate(180deg); }
        .faq-item.open .faq-question { color: #667eea; }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease, padding 0.35s ease;
            padding: 0 22px;
        }
        .faq-item.open .faq-answer {
            max-height: 400px;
            padding: 0 22px 20px;
        }
        .faq-answer p, .faq-answer ul { font-size: 15px; color: #4a5568; line-height: 1.8; }
        .faq-answer a { color: #667eea; font-weight: 600; text-decoration: none; }
        .faq-answer a:hover { text-decoration: underline; }

        @media (max-width: 900px) {
            .help-layout { grid-template-columns: 1fr; }
            .help-sidebar { position: static; }
        }
    </style>
</body>
</html>
