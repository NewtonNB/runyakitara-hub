<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $examples = $_POST['examples'] ?? '';
        $difficulty = $_POST['difficulty'] ?? '';
        
        $stmt = $db->prepare("INSERT INTO grammar_topics (title, content, examples, difficulty, created_at) VALUES (?, ?, ?, ?, datetime('now'))");
        if ($stmt->execute([$title, $content, $examples, $difficulty])) {
            $message = 'Grammar topic added successfully!';
            $messageType = 'success';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $examples = $_POST['examples'] ?? '';
        $difficulty = $_POST['difficulty'] ?? '';
        
        $stmt = $db->prepare("UPDATE grammar_topics SET title=?, content=?, examples=?, difficulty=? WHERE id=?");
        if ($stmt->execute([$title, $content, $examples, $difficulty, $id])) {
            $message = 'Grammar topic updated successfully!';
            $messageType = 'success';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM grammar_topics WHERE id=?");
        if ($stmt->execute([$id])) {
            $message = 'Grammar topic deleted successfully!';
            $messageType = 'success';
        }
    }
}

$topics = [];
try {
    $stmt = $db->query("SELECT * FROM grammar_topics ORDER BY created_at DESC");
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $topics = [];
}

$editTopic = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM grammar_topics WHERE id=?");
    $stmt->execute([$editId]);
    $editTopic = $stmt->fetch(PDO::FETCH_ASSOC);
}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grammar - Runyakitara Hub Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/form-validation.css">
    <style>
        .content-table, .form-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .form-section h2, .content-table h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .grammar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }
        .grammar-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }
        .grammar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .grammar-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 12px;
        }
        .grammar-content {
            font-size: 14px;
            color: var(--text);
            line-height: 1.6;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .grammar-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }
        .difficulty-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .difficulty-easy {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        .difficulty-medium {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        .difficulty-hard {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 16px;
        }
        .btn-edit {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }
        .btn-edit:hover {
            background: var(--info);
            color: white;
        }
        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        .btn-delete:hover {
            background: var(--danger);
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <?php include 'includes/header.php'; ?>
            
            <main class="admin-main">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="bi bi-check-circle"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-section">
                    <h2>
                        <i class="bi bi-<?php echo $editTopic ? 'pencil' : 'plus-circle'; ?>"></i>
                        <?php echo $editTopic ? 'Edit Grammar Topic' : 'Add New Grammar Topic'; ?>
                    </h2>
                    <form method="POST" data-validate="true">
                        <input type="hidden" name="action" value="<?php echo $editTopic ? 'edit' : 'add'; ?>">
                        <?php if ($editTopic): ?>
                            <input type="hidden" name="id" value="<?php echo $editTopic['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="title">Topic Title *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo $editTopic ? htmlspecialchars($editTopic['title']) : ''; ?>"
                                   placeholder="e.g., Noun Classes in Runyakitara">
                        </div>
                        
                        <div class="form-group">
                            <label for="difficulty">Difficulty Level *</label>
                            <select id="difficulty" name="difficulty" required>
                                <option value="">Select Difficulty</option>
                                <option value="easy" <?php echo ($editTopic && $editTopic['difficulty'] === 'easy') ? 'selected' : ''; ?>>Easy</option>
                                <option value="medium" <?php echo ($editTopic && $editTopic['difficulty'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="hard" <?php echo ($editTopic && $editTopic['difficulty'] === 'hard') ? 'selected' : ''; ?>>Hard</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Explanation *</label>
                            <textarea id="content" name="content" required 
                                      placeholder="Explain the grammar topic..."><?php echo $editTopic ? htmlspecialchars($editTopic['content']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="examples">Examples *</label>
                            <textarea id="examples" name="examples" required 
                                      placeholder="Provide examples..."><?php echo $editTopic ? htmlspecialchars($editTopic['examples']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-<?php echo $editTopic ? 'check' : 'plus'; ?>-circle"></i>
                                <?php echo $editTopic ? 'Update Topic' : 'Add Topic'; ?>
                            </button>
                            <?php if ($editTopic): ?>
                                <a href="grammar-manage.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <div class="content-table">
                    <h2><i class="bi bi-pencil-square"></i> Grammar Topics (<?php echo count($topics); ?>)</h2>
                    
                    <?php if (empty($topics)): ?>
                        <div style="text-align: center; padding: 60px; color: var(--text-light);">
                            <i class="bi bi-pencil-square" style="font-size: 64px; opacity: 0.5; display: block; margin-bottom: 16px;"></i>
                            <h3 style="margin-bottom: 8px;">No grammar topics yet</h3>
                            <p>Add your first grammar topic above!</p>
                        </div>
                    <?php else: ?>
                        <div class="grammar-grid">
                            <?php foreach ($topics as $topic): ?>
                                <div class="grammar-card">
                                    <div class="grammar-title"><?php echo htmlspecialchars($topic['title']); ?></div>
                                    <div class="grammar-content"><?php echo htmlspecialchars($topic['content']); ?></div>
                                    <div class="grammar-footer">
                                        <span class="difficulty-badge difficulty-<?php echo $topic['difficulty']; ?>">
                                            <?php echo ucfirst($topic['difficulty']); ?>
                                        </span>
                                        <div class="action-buttons">
                                            <a href="?edit=<?php echo $topic['id']; ?>" class="btn-icon btn-edit" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this topic?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $topic['id']; ?>">
                                                <button type="submit" class="btn-icon btn-delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        document.getElementById('mobileToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
    <script src="js/form-validation.js"></script>
</body>
</html>
