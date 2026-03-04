<?php
/**
 * Proverbs API v1
 */

require_once __DIR__ . '/BaseAPI.php';

class ProverbsAPI extends BaseAPI {
    
    public function handleRequest() {
        $method = $this->getMethod();
        $id = $this->getResourceId();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getProverb($id);
                } else {
                    $this->getProverbs();
                }
                break;
                
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    private function getProverbs() {
        $query = $_GET;
        $page = $query['page'] ?? 1;
        $limit = $query['limit'] ?? 20;
        $search = $query['search'] ?? null;
        
        $sql = "SELECT * FROM proverbs WHERE 1=1";
        
        if ($search) {
            $search = $this->sanitize($search);
            $sql .= " AND (proverb LIKE '%$search%' OR translation LIKE '%$search%' OR meaning LIKE '%$search%')";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $result = $this->paginate($sql, $page, $limit);
        $this->sendResponse($result);
    }
    
    private function getProverb($id) {
        $stmt = $this->db->prepare("SELECT * FROM proverbs WHERE id = ?");
        $stmt->execute([$id]);
        $proverb = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$proverb) {
            $this->sendError('Proverb not found', 404);
        }
        
        $this->sendResponse($proverb);
    }
}

$api = new ProverbsAPI();
$api->handleRequest();
