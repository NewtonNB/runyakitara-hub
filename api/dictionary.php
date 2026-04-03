<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';
try {
    $db       = getDBConnection();
    $search   = $_GET['search']   ?? '';
    $category = $_GET['category'] ?? '';

    $sql    = "SELECT * FROM dictionary WHERE deleted_at IS NULL";
    $params = [];

    if ($search) {
        $sql .= " AND (word_runyakitara LIKE ? OR word_english LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    $sql .= " ORDER BY word_runyakitara ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
