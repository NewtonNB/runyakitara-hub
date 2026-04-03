<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';
try {
    $db    = getDBConnection();
    $level = $_GET['level'] ?? null;
    if ($level) {
        $stmt = $db->prepare("SELECT * FROM lessons WHERE deleted_at IS NULL AND level = ? ORDER BY lesson_order ASC");
        $stmt->execute([$level]);
    } else {
        $stmt = $db->query("SELECT * FROM lessons WHERE deleted_at IS NULL ORDER BY lesson_order ASC");
    }
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
