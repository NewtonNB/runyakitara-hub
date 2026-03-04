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
    <link rel="stylesheet" href="admin/css/form-validation.css">
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
                                <p>info@runyakitara.com</p>
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
                    <form class="contact-form" id="contactForm" data-validate="true" novalidate autocomplete="on">
                        <div class="form-group">
                            <label for="name" class="required">Name</label>
                            <input type="text" id="name" name="name" required minlength="2" maxlength="100" autocomplete="name">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" required autocomplete="email">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject" class="required">Subject</label>
                            <input type="text" id="subject" name="subject" required minlength="5" maxlength="200" autocomplete="off">
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="required">Message</label>
                            <textarea id="message" name="message" rows="6" required minlength="20" autocomplete="off"></textarea>
                            <span class="field-hint">Minimum 20 characters required</span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                    <div id="formMessage" class="form-message"></div>
                </div>
            </div>
        </div>
    </section>
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
    
    <script src="admin/js/form-validation.js"></script>
    
    <script>
        // Contact form submission - Button click handler
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contactForm');
            const formMessage = document.getElementById('formMessage');
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            
            console.log('Contact form initialized');
            
            // Attach to button click instead of form submit
            submitBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Button clicked!');
                
                // Check if form is valid
                if (!contactForm.checkValidity()) {
                    console.log('Form is invalid, triggering validation');
                    contactForm.reportValidity();
                    return;
                }
                
                console.log('Form is valid, submitting...');
                
                const originalBtnText = this.innerHTML;
                
                // Disable button and show loading
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';
                formMessage.style.display = 'none';
                
                // Get form data
                const formData = new FormData(contactForm);
                
                console.log('Form data:', {
                    name: formData.get('name'),
                    email: formData.get('email'),
                    subject: formData.get('subject'),
                    message: formData.get('message')
                });
                
                try {
                    console.log('Sending to api/contact.php...');
                    
                    const response = await fetch('api/contact.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    console.log('Response status:', response.status);
                    
                    const text = await response.text();
                    console.log('Response text:', text);
                    
                    let result;
                    try {
                        result = JSON.parse(text);
                        console.log('Parsed result:', result);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        throw new Error('Invalid response from server');
                    }
                    
                    if (result.success) {
                        console.log('SUCCESS! Showing message...');
                        formMessage.className = 'form-message success';
                        formMessage.innerHTML = `
                            <div>
                                <strong>Thank you for reaching out!</strong><br>
                                We've received your message and will get back to you within 24-48 hours.
                            </div>
                        `;
                        formMessage.style.display = 'flex';
                        
                        // Clear form
                        contactForm.reset();
                        
                        // Remove validation classes
                        contactForm.querySelectorAll('.valid, .invalid').forEach(el => {
                            el.classList.remove('valid', 'invalid');
                        });
                        
                        // Scroll to message
                        setTimeout(() => {
                            formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 100);
                        
                        // Hide message after 8 seconds
                        setTimeout(() => {
                            formMessage.style.opacity = '0';
                            setTimeout(() => {
                                formMessage.style.display = 'none';
                                formMessage.style.opacity = '1';
                            }, 300);
                        }, 8000);
                    } else {
                        console.log('ERROR response:', result.message);
                        formMessage.className = 'form-message error';
                        formMessage.innerHTML = `
                            <div>
                                <strong>Oops! Something went wrong.</strong><br>
                                ${result.message || 'Please try again or contact us directly via email.'}
                            </div>
                        `;
                        formMessage.style.display = 'flex';
                        setTimeout(() => {
                            formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 100);
                    }
                } catch (error) {
                    console.error('Submission error:', error);
                    formMessage.className = 'form-message error';
                    formMessage.innerHTML = `
                        <div>
                            <strong>Connection Error</strong><br>
                            ${error.message || 'Please check your internet connection and try again.'}
                        </div>
                    `;
                    formMessage.style.display = 'flex';
                    setTimeout(() => {
                        formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 100);
                } finally {
                    this.disabled = false;
                    this.innerHTML = originalBtnText;
                    console.log('Done!');
                }
            });
            
            // Also prevent form submit
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Form submit prevented');
            });
        });
    </script>
</body>
</html>
