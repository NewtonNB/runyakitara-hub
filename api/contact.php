<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent any output before JSON
ob_start();

require_once '../config/database.php';

// ============================================
// CONFIGURATION - UPDATE WITH YOUR EMAIL
// ============================================
$ADMIN_EMAIL = 'runyakitarahub22@gmail.com'; // Admin email to receive messages
$SITE_NAME = 'Runyakitara Hub';
$SITE_URL = 'http://localhost:8000'; // Update with your domain

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Sanitize inputs
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

try {
    // Save to database
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, datetime('now'))");
    $dbSaved = $stmt->execute([$name, $email, $subject, $message]);
    
    closeDBConnection($db);
    
    // ============================================
    // PHPMailer Implementation
    // ============================================
    require_once '../libs/PHPMailer/src/Exception.php';
    require_once '../libs/PHPMailer/src/PHPMailer.php';
    require_once '../libs/PHPMailer/src/SMTP.php';

    if ($dbSaved) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mailSent = false;
        $mailError = '';
        
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $ADMIN_EMAIL;
            $mail->Password   = 'pjmkupxqstwocxwj';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($ADMIN_EMAIL, $SITE_NAME);
            $mail->addAddress($ADMIN_EMAIL);
            $mail->addReplyTo($email, $name);

            $mail->isHTML(false);
            $mail->Subject = "New Contact Message: $subject";
            $mail->Body    = "You have received a new message from the contact form.\n\n" .
                             "Name: $name\n" .
                             "Email: $email\n" .
                             "Subject: $subject\n\n" .
                             "Message:\n$message\n\n" .
                             "---\nThis email was sent from the $SITE_NAME contact form.";

            $mailSent = $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $mailError = $e->getMessage();
            error_log("PHPMailer Error: " . $mailError);
        }

        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We will get back to you within 24-48 hours.',
            'mail_sent' => $mailSent,
            'mail_error' => $mailError // remove this line in production
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to save message. Please try again.'
        ]);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    error_log('Contact form error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
exit;
