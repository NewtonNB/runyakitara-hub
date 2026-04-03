<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';
try {
    $db   = getDBConnection();
    $stmt = $db->query("SELECT * FROM grammar_topics WHERE deleted_at IS NULL ORDER BY created_at ASC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
