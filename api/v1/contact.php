<?php
/**
 * Contact API v1
 */

require_once __DIR__ . '/BaseAPI.php';

class ContactAPI extends BaseAPI {
    
    public function handleRequest() {
        $method = $this->getMethod();
        
        switch ($method) {
            case 'POST':
                $this->submitContact();
                break;
                
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    private function submitContact() {
        $data = $this->getBody();
        
        // Validate required fields
        $this->validateRequired($data, ['name', 'email', 'subject', 'message']);
        
        // Sanitize input
        $name = $this->sanitize($data['name']);
        $email = $this->sanitize($data['email']);
        $subject = $this->sanitize($data['subject']);
        $message = $this->sanitize($data['message']);
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendError('Invalid email address', 400);
        }
        
        // Insert into database
        try {
            $stmt = $this->db->prepare("
                INSERT INTO messages (name, email, subject, message, created_at) 
                VALUES (?, ?, ?, ?, datetime('now'))
            ");
            
            $stmt->execute([$name, $email, $subject, $message]);
            
            $this->sendResponse([
                'message' => 'Contact form submitted successfully',
                'id' => $this->db->lastInsertId()
            ], 201);
            
        } catch (Exception $e) {
            $this->sendError('Failed to submit contact form', 500);
        }
    }
}

$api = new ContactAPI();
$api->handleRequest();
