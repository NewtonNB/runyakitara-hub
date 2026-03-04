<?php
/**
 * Translations API v1
 */

require_once __DIR__ . '/BaseAPI.php';

class TranslationsAPI extends BaseAPI {
    
    public function handleRequest() {
        $method = $this->getMethod();
        $id = $this->getResourceId();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getTranslation($id);
                } else {
                    $this->getTranslations();
                }
                break;
                
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    private function getTranslations() {
        $query = $_GET;
        $page = $query['page'] ?? 1;
        $limit = $query['limit'] ?? 20;
        $type = $query['type'] ?? null;
        
        $sql = "SELECT * FROM translations WHERE 1=1";
        
        if ($type) {
            $type = $this->sanitize($type);
            $sql .= " AND type = '$type'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $result = $this->paginate($sql, $page, $limit);
        $this->sendResponse($result);
    }
    
    private function getTranslation($id) {
        $stmt = $this->db->prepare("SELECT * FROM translations WHERE id = ?");
        $stmt->execute([$id]);
        $translation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$translation) {
            $this->sendError('Translation not found', 404);
        }
        
        $this->sendResponse($translation);
    }
}

$api = new TranslationsAPI();
$api->handleRequest();
