<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    
    $stmt = $db->prepare("SELECT * FROM proverbs ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    
    $proverbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $proverbs,
        'count' => count($proverbs)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
