<?php
/**
 * Dictionary API v1
 * Endpoints for dictionary words
 */

require_once __DIR__ . '/BaseAPI.php';

class DictionaryAPI extends BaseAPI {
    
    public function handleRequest() {
        $method = $this->getMethod();
        $id = $this->getResourceId();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getWord($id);
                } else {
                    $this->getWords();
                }
                break;
                
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    /**
     * Get all words with optional filtering and pagination
     */
    private function getWords() {
        $query = $_GET;
        $page = $query['page'] ?? 1;
        $limit = $query['limit'] ?? 20;
        $search = $query['search'] ?? null;
        $category = $query['category'] ?? null;
        
        $sql = "SELECT * FROM dictionary WHERE 1=1";
        
        if ($search) {
            $search = $this->sanitize($search);
            $sql .= " AND (word LIKE '%$search%' OR translation LIKE '%$search%')";
        }
        
        if ($category) {
            $category = $this->sanitize($category);
            $sql .= " AND category = '$category'";
        }
        
        $sql .= " ORDER BY word ASC";
        
        $result = $this->paginate($sql, $page, $limit);
        $this->sendResponse($result);
    }
    
    /**
     * Get single word by ID
     */
    private function getWord($id) {
        $stmt = $this->db->prepare("SELECT * FROM dictionary WHERE id = ?");
        $stmt->execute([$id]);
        $word = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$word) {
            $this->sendError('Word not found', 404);
        }
        
        $this->sendResponse($word);
    }
}

// Handle the request
$api = new DictionaryAPI();
$api->handleRequest();
