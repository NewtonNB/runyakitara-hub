<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $title = $_POST['title'] ?? '';
        $level = $_POST['level'] ?? '';
        $content = $_POST['content'] ?? '';
        $vocabulary = $_POST['vocabulary'] ?? '';
        
        // Auto-calculate lesson_order (get max + 1)
        $orderStmt = $db->query("SELECT COALESCE(MAX(lesson_order), 0) + 1 as next_order FROM lessons");
        $nextOrder = $orderStmt->fetch(PDO::FETCH_ASSOC)['next_order'];
        
        $stmt = $db->prepare("INSERT INTO lessons (title, level, content, vocabulary, lesson_order, created_at) VALUES (?, ?, ?, ?, ?, datetime('now'))");
        if ($stmt->execute([$title, $level, $content, $vocabulary, $nextOrder])) {
            $message = 'Lesson added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error adding lesson.';
            $messageType = 'error';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $level = $_POST['level'] ?? '';
        $content = $_POST['content'] ?? '';
        $vocabulary = $_POST['vocabulary'] ?? '';
        
        $stmt = $db->prepare("UPDATE lessons SET title=?, level=?, content=?, vocabulary=? WHERE id=?");
        if ($stmt->execute([$title, $level, $content, $vocabulary, $id])) {
            $message = 'Lesson updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating lesson.';
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM lessons WHERE id=?");
        if ($stmt->execute([$id])) {
            $message = 'Lesson deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting lesson.';
            $messageType = 'error';
        }
    }
}

// Get all lessons
$lessons = [];
try {
    $stmt = $db->query("SELECT * FROM lessons ORDER BY created_at DESC");
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $lessons = [];
}

// Get lesson for editing
$editLesson = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM lessons WHERE id=?");
    $stmt->execute([$editId]);
    $editLesson = $stmt->fetch(PDO::FETCH_ASSOC);
}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lessons - Runyakitara Hub Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/form-validation.css">
    
    <style>
        .content-table {
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: var(--light);
        }
        
        th {
            padding: 16px 24px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            color: var(--text);
        }
        
        tbody tr {
            transition: var(--transition);
        }
        
        tbody tr:hover {
            background: var(--light);
        }
        
        .level-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .level-beginner {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .level-intermediate {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .level-advanced {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
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
        
        .form-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .form-section h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
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
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
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
                        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add/Edit Form -->
                <div class="form-section">
                    <h2>
                        <i class="bi bi-<?php echo $editLesson ? 'pencil' : 'plus-circle'; ?>"></i>
                        <?php echo $editLesson ? 'Edit Lesson' : 'Add New Lesson'; ?>
                    </h2>
                    <form method="POST" data-validate="true">
                        <input type="hidden" name="action" value="<?php echo $editLesson ? 'edit' : 'add'; ?>">
                        <?php if ($editLesson): ?>
                            <input type="hidden" name="id" value="<?php echo $editLesson['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="title" class="required">Lesson Title</label>
                            <input type="text" id="title" name="title" required minlength="5" maxlength="200"
                                   value="<?php echo $editLesson ? htmlspecialchars($editLesson['title']) : ''; ?>"
                                   placeholder="e.g., Greetings and Introductions">
                            <span class="field-hint">5-200 characters required</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="level" class="required">Level</label>
                            <select id="level" name="level" required>
                                <option value="">Select Level</option>
                                <option value="beginner" <?php echo ($editLesson && $editLesson['level'] === 'beginner') ? 'selected' : ''; ?>>Beginner</option>
                                <option value="intermediate" <?php echo ($editLesson && $editLesson['level'] === 'intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="advanced" <?php echo ($editLesson && $editLesson['level'] === 'advanced') ? 'selected' : ''; ?>>Advanced</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="content" class="required">Lesson Content</label>
                            <textarea id="content" name="content" required minlength="50"
                                      placeholder="Enter the lesson content..."><?php echo $editLesson ? htmlspecialchars($editLesson['content']) : ''; ?></textarea>
                            <span class="field-hint">Minimum 50 characters required</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="vocabulary">Vocabulary (Optional)</label>
                            <textarea id="vocabulary" name="vocabulary" 
                                      placeholder="Enter vocabulary words and meanings..."><?php echo $editLesson ? htmlspecialchars($editLesson['vocabulary']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-<?php echo $editLesson ? 'check' : 'plus'; ?>-circle"></i>
                                <?php echo $editLesson ? 'Update Lesson' : 'Add Lesson'; ?>
                            </button>
                            <?php if ($editLesson): ?>
                                <a href="lessons-manage.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Lessons Table -->
                <div class="content-table">
                    <div class="table-header">
                        <h2><i class="bi bi-book"></i> All Lessons (<?php echo count($lessons); ?>)</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Level</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lessons)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-light);">
                                        <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 12px; opacity: 0.5;"></i>
                                        No lessons found. Add your first lesson above!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lessons as $lesson): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($lesson['title']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="level-badge level-<?php echo $lesson['level']; ?>">
                                                <?php echo ucfirst($lesson['level']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($lesson['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?edit=<?php echo $lesson['id']; ?>" class="btn-icon btn-edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this lesson?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $lesson['id']; ?>">
                                                    <button type="submit" class="btn-icon btn-delete" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
