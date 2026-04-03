<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';
try {
    $db   = getDBConnection();
    $type = $_GET['type'] ?? null;
    if ($type) {
        $stmt = $db->prepare("SELECT * FROM translations WHERE deleted_at IS NULL AND type = ? ORDER BY created_at DESC");
        $stmt->execute([$type]);
    } else {
        $stmt = $db->query("SELECT * FROM translations WHERE deleted_at IS NULL ORDER BY created_at DESC");
    }
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
