<?php
/**
 * Grammar API v1
 * Endpoints for grammar topics
 */

require_once __DIR__ . '/BaseAPI.php';

class GrammarAPI extends BaseAPI {
    
    public function handleRequest() {
        $method = $this->getMethod();
        $id = $this->getResourceId();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getTopic($id);
                } else {
                    $this->getTopics();
                }
                break;
                
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    private function getTopics() {
        $query = $_GET;
        $page = $query['page'] ?? 1;
        $limit = $query['limit'] ?? 20;
        $difficulty = $query['difficulty'] ?? null;
        
        $sql = "SELECT * FROM grammar_topics WHERE 1=1";
        
        if ($difficulty) {
            $difficulty = $this->sanitize($difficulty);
            $sql .= " AND difficulty = '$difficulty'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $result = $this->paginate($sql, $page, $limit);
        $this->sendResponse($result);
    }
    
    private function getTopic($id) {
        $stmt = $this->db->prepare("SELECT * FROM grammar_topics WHERE id = ?");
        $stmt->execute([$id]);
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$topic) {
            $this->sendError('Grammar topic not found', 404);
        }
        
        $this->sendResponse($topic);
    }
}

$api = new GrammarAPI();
$api->handleRequest();
