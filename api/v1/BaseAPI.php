<?php
/**
 * Base API Class for v1
 * Provides common functionality for all API endpoints
 */

class BaseAPI {
    protected $db;
    protected $version = 'v1';
    
    public function __construct() {
        require_once __DIR__ . '/../../config/database.php';
        $this->db = getDBConnection();
    }
    
    /**
     * Send JSON response
     */
    protected function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'version' => $this->version,
            'data' => $data,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send error response
     */
    protected function sendError($message, $statusCode = 400, $errors = null) {
        http_response_code($statusCode);
        $response = [
            'success' => false,
            'version' => $this->version,
            'error' => $message,
            'timestamp' => date('c')
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Get request method
     */
    protected function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Get request body
     */
    protected function getBody() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    /**
     * Get query parameters
     */
    protected function getQuery() {
        return $_GET;
    }
    
    /**
     * Get resource ID from URL
     */
    protected function getResourceId() {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = explode('/', trim($uri, '/'));
        $id = end($parts);
        return is_numeric($id) ? (int)$id : null;
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($data, $required) {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->sendError(
                'Missing required fields',
                400,
                ['missing_fields' => $missing]
            );
        }
    }
    
    /**
     * Sanitize input
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Paginate results
     */
    protected function paginate($query, $page = 1, $limit = 20) {
        $page = max(1, (int)$page);
        $limit = min(100, max(1, (int)$limit));
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $countQuery = preg_replace('/SELECT .* FROM/i', 'SELECT COUNT(*) as total FROM', $query);
        $stmt = $this->db->query($countQuery);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get paginated results
        $query .= " LIMIT $limit OFFSET $offset";
        $stmt = $this->db->query($query);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'items' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }
    
    /**
     * Close database connection
     */
    public function __destruct() {
        if ($this->db) {
            closeDBConnection($this->db);
        }
    }
}
