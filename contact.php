<?php
$currentPage = 'contact';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Runyakitara Hub</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <section class="page-hero">
        <div class="container">
            <div class="hero-content" data-aos="fade-up">
                <h1><i class="bi bi-envelope"></i> Contact Us</h1>
                <p>Get in touch with us</p>
            </div>
        </div>
    </section>
    
    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info" data-aos="fade-right">
                    <h2>Get In Touch</h2>
                    <p>Have questions about Runyakitara languages or our platform? We'd love to hear from you!</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="bi bi-envelope-fill"></i>
                            <div>
                                <h4>Email</h4>
                                <p>runyakitarahub22@gmail.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-geo-alt-fill"></i>
                            <div>
                                <h4>Location</h4>
                                <p>Southwestern Uganda</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-clock-fill"></i>
                            <div>
                                <h4>Response Time</h4>
                                <p>Within 24-48 hours</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-wrapper" data-aos="fade-left">
                    <form class="contact-form" id="contactForm" method="post" novalidate autocomplete="on">
                        <div class="form-group">
                            <label for="name" class="required">Name</label>
                            <input type="text" id="name" name="name" required minlength="2" maxlength="100" autocomplete="name" placeholder="Your full name">
                            <div class="field-error" id="nameError"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" required autocomplete="email" placeholder="your@email.com">
                            <div class="field-error" id="emailError"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject" class="required">Subject</label>
                            <input type="text" id="subject" name="subject" required minlength="5" maxlength="200" autocomplete="off" placeholder="What is this about?">
                            <div class="field-error" id="subjectError"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="required">Message</label>
                            <textarea id="message" name="message" rows="6" required minlength="20" autocomplete="off" placeholder="Write your message here..."></textarea>
                            <div class="field-error" id="messageError"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="sendBtn">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>

                    <!-- Success Card (hidden by default) -->
                    <div id="successCard" style="display:none; text-align:center; padding: 50px 30px; animation: fadeInUp 0.6s ease;">
                        <div style="width:80px; height:80px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display:flex; align-items:center; justify-content:center; margin: 0 auto 24px; box-shadow: 0 10px 30px rgba(102,126,234,0.4);">
                            <i class="bi bi-check-lg" style="font-size:36px; color:white;"></i>
                        </div>
                        <h3 style="font-size:24px; font-weight:700; color:#1a1a2e; margin-bottom:12px;">Message Sent!</h3>
                        <p style="color:#666; font-size:16px; line-height:1.6; margin-bottom:28px;">
                            Thank you for reaching out. We've received your message and will get back to you within <strong>24-48 hours</strong>.
                        </p>
                        <button onclick="resetContactForm()" style="background: linear-gradient(135deg, #667eea, #764ba2); color:white; border:none; padding:12px 28px; border-radius:50px; font-size:15px; cursor:pointer; font-weight:600; box-shadow: 0 4px 15px rgba(102,126,234,0.3); transition: all 0.3s;">
                            <i class="bi bi-arrow-left"></i> Send Another Message
                        </button>
                    </div>

                    <div id="formMessage" class="form-message"></div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script>
        AOS.init({ duration: 800, once: true, offset: 100 });

        function showError(fieldId, errorId, msg) {
            var el = document.getElementById(fieldId);
            var err = document.getElementById(errorId);
            el.style.borderColor = '#ef4444';
            err.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> ' + msg;
            err.style.display = 'flex';
        }

        function clearError(fieldId, errorId) {
            var el = document.getElementById(fieldId);
            var err = document.getElementById(errorId);
            el.style.borderColor = '';
            err.style.display = 'none';
        }

        function validateForm() {
            var valid = true;
            var name    = document.getElementById('name').value.trim();
            var email   = document.getElementById('email').value.trim();
            var subject = document.getElementById('subject').value.trim();
            var message = document.getElementById('message').value.trim();

            clearError('name','nameError');
            clearError('email','emailError');
            clearError('subject','subjectError');
            clearError('message','messageError');

            if (!name || name.length < 2) {
                showError('name','nameError','Please enter your name (at least 2 characters).');
                valid = false;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('email','emailError','Please enter a valid email address.');
                valid = false;
            }
            if (!subject || subject.length < 5) {
                showError('subject','subjectError','Subject must be at least 5 characters.');
                valid = false;
            }
            if (!message || message.length < 20) {
                showError('message','messageError','Message must be at least 20 characters.');
                valid = false;
            }
            return valid;
        }

        // Live validation on blur
        ['name','email','subject','message'].forEach(function(id) {
            document.getElementById(id).addEventListener('blur', function() { validateForm(); });
            document.getElementById(id).addEventListener('input', function() {
                clearError(id, id + 'Error');
                this.style.borderColor = this.value.trim() ? '#10b981' : '';
            });
        });

        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!validateForm()) return;

            var form = this;
            var btn = document.getElementById('sendBtn');
            var originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';

            fetch('api/contact.php', { method: 'POST', body: new FormData(form) })
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.text();
                })
                .then(function(text) {
                    var result;
                    try { result = JSON.parse(text); }
                    catch(e) {
                        console.error('Server response:', text);
                        throw new Error('Invalid server response');
                    }
                    if (result.success) {
                        form.style.transition = 'opacity 0.4s';
                        form.style.opacity = '0';
                        setTimeout(function() {
                            form.style.display = 'none';
                            var card = document.getElementById('successCard');
                            card.style.display = 'block';
                            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 400);
                    } else {
                        var msg = document.getElementById('formMessage');
                        msg.className = 'form-message error';
                        msg.innerHTML = '<div><strong>Error:</strong> ' + (result.message || 'Please try again.') + '</div>';
                        msg.style.display = 'flex';
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(function(err) {
                    var msg = document.getElementById('formMessage');
                    msg.className = 'form-message error';
                    msg.innerHTML = '<div><strong>Connection error:</strong> ' + err.message + '. Check console for details.</div>';
                    msg.style.display = 'flex';
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });

        function resetContactForm() {
            var form = document.getElementById('contactForm');
            var successCard = document.getElementById('successCard');
            successCard.style.display = 'none';
            form.reset();
            ['name','email','subject','message'].forEach(function(id) {
                clearError(id, id + 'Error');
                document.getElementById(id).style.borderColor = '';
            });
            form.style.opacity = '0';
            form.style.display = 'block';
            setTimeout(function() { form.style.opacity = '1'; }, 50);
        }
    </script>

    <style>
        .field-error {
            display: none;
            align-items: center;
            gap: 6px;
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
            font-weight: 500;
        }
        .field-error i { font-size: 14px; flex-shrink: 0; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
