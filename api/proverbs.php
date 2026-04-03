<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';
try {
    $db    = getDBConnection();
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
    $stmt  = $db->prepare("SELECT * FROM proverbs WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
