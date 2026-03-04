<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    $stmt = $db->prepare("SELECT * FROM articles ORDER BY published_date DESC LIMIT ?");
    $stmt->execute([$limit]);
    
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $articles,
        'count' => count($articles)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
