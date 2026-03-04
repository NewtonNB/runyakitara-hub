<?php
/**
 * Lessons API v1
 * Endpoints for language lessons
 */

require_once __DIR__ . '/BaseAPI.php';

class LessonsAPI extends BaseAPI {
    
    public function handleRequest() {
        $method = $this->getMethod();
        $id = $this->getResourceId();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getLesson($id);
                } else {
                    $this->getLessons();
                }
                break;
                
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    /**
     * Get all lessons with optional filtering and pagination
     */
    private function getLessons() {
        $query = $_GET;
        $page = $query['page'] ?? 1;
        $limit = $query['limit'] ?? 20;
        $level = $query['level'] ?? null;
        
        $sql = "SELECT * FROM lessons WHERE 1=1";
        
        if ($level) {
            $level = $this->sanitize($level);
            $sql .= " AND level = '$level'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $result = $this->paginate($sql, $page, $limit);
        $this->sendResponse($result);
    }
    
    /**
     * Get single lesson by ID
     */
    private function getLesson($id) {
        $stmt = $this->db->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$id]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lesson) {
            $this->sendError('Lesson not found', 404);
        }
        
        $this->sendResponse($lesson);
    }
}

// Handle the request
$api = new LessonsAPI();
$api->handleRequest();
