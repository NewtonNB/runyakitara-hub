<?php
/**
 * Articles API v1
 */

require_once __DIR__ . '/BaseAPI.php';

class ArticlesAPI extends BaseAPI {
    
    public function handleRequest() {
        $method = $this->getMethod();
        $id = $this->getResourceId();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getArticle($id);
                } else {
                    $this->getArticles();
                }
                break;
                
            default:
                $this->sendError('Method not allowed', 405);
        }
    }
    
    private function getArticles() {
        $query = $_GET;
        $page = $query['page'] ?? 1;
        $limit = $query['limit'] ?? 20;
        $category = $query['category'] ?? null;
        $author = $query['author'] ?? null;
        
        $sql = "SELECT * FROM articles WHERE 1=1";
        
        if ($category) {
            $category = $this->sanitize($category);
            $sql .= " AND category = '$category'";
        }
        
        if ($author) {
            $author = $this->sanitize($author);
            $sql .= " AND author LIKE '%$author%'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $result = $this->paginate($sql, $page, $limit);
        $this->sendResponse($result);
    }
    
    private function getArticle($id) {
        $stmt = $this->db->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$article) {
            $this->sendError('Article not found', 404);
        }
        
        $this->sendResponse($article);
    }
}

$api = new ArticlesAPI();
$api->handleRequest();
