<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ob_start();

require_once '../config/database.php';

$ADMIN_EMAIL = 'runyakitarahub22@gmail.com';
$SITE_NAME   = 'Runyakitara Hub';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if (!$name || !$email || !$subject || !$message) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Sanitize
$name    = htmlspecialchars($name,    ENT_QUOTES, 'UTF-8');
$email   = filter_var($email, FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

try {
    // Save to database — this is the primary action
    $db   = getDBConnection();
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, 'new', datetime('now'))");
    $dbSaved = $stmt->execute([$name, $email, $subject, $message]);
    closeDBConnection($db);

    if (!$dbSaved) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to save message. Please try again.']);
        exit;
    }

    // Try to send email notification — failure here does NOT block success
    $mailSent  = false;
    $mailError = '';
    try {
        require_once '../libs/PHPMailer/src/Exception.php';
        require_once '../libs/PHPMailer/src/PHPMailer.php';
        require_once '../libs/PHPMailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $ADMIN_EMAIL;
        $mail->Password   = 'pjmkupxqstwocxwj';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 10; // don't hang forever

        $mail->setFrom($ADMIN_EMAIL, $SITE_NAME);
        $mail->addAddress($ADMIN_EMAIL);
        $mail->addReplyTo($email, $name);
        $mail->isHTML(false);
        $mail->Subject = "New Contact Message: $subject";
        $mail->Body    = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";

        $mailSent = $mail->send();
    } catch (\Exception $e) {
        $mailError = $e->getMessage();
        error_log("PHPMailer Error: " . $mailError);
    }

    // Respond success regardless of email — message is saved in DB
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We will get back to you within 24-48 hours.'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    error_log('Contact form error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
exit;
