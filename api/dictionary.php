<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getDBConnection();

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : null;

$sql = "SELECT * FROM dictionary WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $sql .= " AND (word_runyakitara LIKE ? OR word_english LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY word_runyakitara ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$words = [];
while ($row = $result->fetch_assoc()) {
    $words[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $words
]);

$stmt->close();
closeDBConnection($conn);
?>
