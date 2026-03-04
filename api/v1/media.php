<?php
/**
 * Media API v1
 */

require_once __DIR__ . '/BaseAPI.php';

class MediaAPI extends BaseAPI {
    
    public function handleRequest() {
        $method = $this->getMethod();
        $id = $this->getResourceId();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getMedia($id);
                } else {
                    $this->getAllMedia();
                }
                break;
                
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    private function getAllMedia() {
        $query = $_GET;
        $page = $query['page'] ?? 1;
        $limit = $query['limit'] ?? 20;
        $type = $query['type'] ?? null;
        
        $sql = "SELECT * FROM media WHERE 1=1";
        
        if ($type) {
            $type = $this->sanitize($type);
            $sql .= " AND type = '$type'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $result = $this->paginate($sql, $page, $limit);
        $this->sendResponse($result);
    }
    
    private function getMedia($id) {
        $stmt = $this->db->prepare("SELECT * FROM media WHERE id = ?");
        $stmt->execute([$id]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$media) {
            $this->sendError('Media not found', 404);
        }
        
        $this->sendResponse($media);
    }
}

$api = new MediaAPI();
$api->handleRequest();
