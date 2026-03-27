<?php
/**
 * Engagement API — Likes & Comments
 * POST /api/engagement.php
 * GET  /api/engagement.php?type=proverb&id=1
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../config/database.php';
$db = getDBConnection();

$method = $_SERVER['REQUEST_METHOD'];
$type   = $_GET['type'] ?? $_POST['type'] ?? '';
$id     = (int)($_GET['id']   ?? $_POST['id']   ?? 0);
$action = $_POST['action'] ?? '';
$ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Validate content type
$allowed = ['proverb', 'grammar', 'lesson', 'article', 'translation'];
if (!in_array($type, $allowed) || $id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid type or id']);
    exit;
}

// ── GET: fetch likes count + comments ──────────────────────────────────────
if ($method === 'GET') {
    $likeCount = $db->prepare("SELECT COUNT(*) FROM likes WHERE content_type=? AND content_id=?");
    $likeCount->execute([$type, $id]);
    $likes = (int)$likeCount->fetchColumn();

    $userLiked = $db->prepare("SELECT 1 FROM likes WHERE content_type=? AND content_id=? AND ip_address=?");
    $userLiked->execute([$type, $id, $ip]);
    $liked = (bool)$userLiked->fetchColumn();

    $commentsStmt = $db->prepare(
        "SELECT id, name, comment, created_at FROM comments
         WHERE content_type=? AND content_id=? AND status='approved'
         ORDER BY created_at DESC LIMIT 50"
    );
    $commentsStmt->execute([$type, $id]);
    $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'likes' => $likes, 'liked' => $liked, 'comments' => $comments]);
    exit;
}

// ── POST: like or comment ───────────────────────────────────────────────────
if ($method === 'POST') {

    if ($action === 'like') {
        // Toggle like
        $check = $db->prepare("SELECT id FROM likes WHERE content_type=? AND content_id=? AND ip_address=?");
        $check->execute([$type, $id, $ip]);
        if ($check->fetch()) {
            $db->prepare("DELETE FROM likes WHERE content_type=? AND content_id=? AND ip_address=?")->execute([$type, $id, $ip]);
            $liked = false;
        } else {
            $db->prepare("INSERT INTO likes (content_type, content_id, ip_address) VALUES (?,?,?)")->execute([$type, $id, $ip]);
            $liked = true;
        }
        $count = (int)$db->prepare("SELECT COUNT(*) FROM likes WHERE content_type=? AND content_id=?")->execute([$type, $id]) ? 
                 $db->query("SELECT COUNT(*) FROM likes WHERE content_type='$type' AND content_id=$id")->fetchColumn() : 0;
        echo json_encode(['success' => true, 'liked' => $liked, 'likes' => (int)$count]);
        exit;
    }

    if ($action === 'comment') {
        $name    = trim($_POST['name'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        if (strlen($name) < 2 || strlen($comment) < 3) {
            echo json_encode(['success' => false, 'error' => 'Name and comment are required']);
            exit;
        }
        $name    = htmlspecialchars(substr($name, 0, 80));
        $comment = htmlspecialchars(substr($comment, 0, 1000));

        $stmt = $db->prepare("INSERT INTO comments (content_type, content_id, name, comment) VALUES (?,?,?,?)");
        $stmt->execute([$type, $id, $name, $comment]);

        echo json_encode([
            'success' => true,
            'comment' => [
                'id'         => $db->lastInsertId(),
                'name'       => $name,
                'comment'    => $comment,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
