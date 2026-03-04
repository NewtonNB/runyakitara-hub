<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    $level = isset($_GET['level']) ? $_GET['level'] : null;
    
    if ($level) {
        $stmt = $db->prepare("SELECT * FROM lessons WHERE level = ? ORDER BY lesson_order ASC");
        $stmt->execute([$level]);
    } else {
        $stmt = $db->query("SELECT * FROM lessons ORDER BY lesson_order ASC");
    }
    
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $lessons,
        'count' => count($lessons)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
