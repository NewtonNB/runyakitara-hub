<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $db = getDBConnection();
    
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    
    if ($type) {
        $stmt = $db->prepare("SELECT * FROM translations WHERE type = ? ORDER BY created_at DESC");
        $stmt->execute([$type]);
    } else {
        $stmt = $db->query("SELECT * FROM translations ORDER BY created_at DESC");
    }
    
    $translations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $translations,
        'count' => count($translations)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
