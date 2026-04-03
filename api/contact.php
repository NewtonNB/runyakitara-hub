<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

set_time_limit(60); // give enough time for SMTP
ob_start();

require_once '../config/database.php';

$ADMIN_EMAIL = 'tukamuhebwanewton@gmail.com';
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
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, 'new', NOW())");
    $dbSaved = $stmt->execute([$name, $email, $subject, $message]);
    closeDBConnection($db);

    if (!$dbSaved) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to save message. Please try again.']);
        exit;
    }

    // Respond to client FIRST, then send email in background
    ob_end_clean();
    // Send response immediately
    $response = json_encode([
        'success'     => true,
        'message'     => 'Thank you for your message! We will get back to you within 24-48 hours.',
        'email_sent'  => false,
        'email_error' => null
    ]);

    // Flush response to browser before SMTP
    header('Content-Length: ' . strlen($response));
    header('Connection: close');
    echo $response;
    if (ob_get_level()) ob_end_flush();
    flush();

    // Now send email after response is sent
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
        $mail->Username   = 'tukamuhebwanewton@gmail.com';
        $mail->Password   = 'pjmkupxqstwocxwj';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 15;

        // Notification to admin
        $mail->setFrom($ADMIN_EMAIL, $SITE_NAME);
        $mail->addAddress($ADMIN_EMAIL);
        $mail->addReplyTo($email, $name);
        $mail->isHTML(false);
        $mail->Subject = "New Contact Message: $subject";
        $mail->Body    = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
        $mail->send();

        // Auto-reply to sender
        $mail->clearAddresses();
        $mail->clearReplyTos();
        $mail->addAddress($email, $name);
        $mail->setFrom($ADMIN_EMAIL, $SITE_NAME);
        $mail->Subject = "We received your message — $SITE_NAME";
        $mail->isHTML(true);
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:30px;'>
                <h2 style='color:#667eea;'>Thank you, $name!</h2>
                <p style='color:#334155;font-size:15px;line-height:1.7;'>
                    We have received your message and will get back to you within <strong>24–48 hours</strong>.
                </p>
                <div style='background:#f8fafc;border-left:4px solid #667eea;padding:16px 20px;border-radius:8px;margin:20px 0;'>
                    <p style='margin:0;color:#64748b;font-size:13px;'><strong>Your message:</strong></p>
                    <p style='margin:8px 0 0;color:#334155;font-size:14px;'>$message</p>
                </div>
                <p style='color:#64748b;font-size:13px;'>
                    If you have any urgent questions, feel free to reach us at
                    <a href='mailto:$ADMIN_EMAIL' style='color:#667eea;'>$ADMIN_EMAIL</a>.
                </p>
                <hr style='border:none;border-top:1px solid #e2e8f0;margin:24px 0;'>
                <p style='color:#94a3b8;font-size:12px;text-align:center;'>
                    &copy; " . date('Y') . " $SITE_NAME &mdash; Preserving language, celebrating culture.
                </p>
            </div>";
        $mail->AltBody = "Thank you $name! We received your message and will reply within 24-48 hours.\n\nYour message: $message\n\n$SITE_NAME";
        $mail->send();
    } catch (\Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
    }

} catch (Exception $e) {
    ob_end_clean();
    error_log('Contact form error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
exit;
