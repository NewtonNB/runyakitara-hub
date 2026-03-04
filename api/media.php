<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    
    $query = "SELECT * FROM media WHERE 1=1";
    $params = [];
    
    if ($type) {
        $query .= " AND type = ?";
        $params[] = $type;
    }
    
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $media,
        'count' => count($media)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
