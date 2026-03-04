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
        $type = $_POST['type'] ?? '';
        $original_text = $_POST['original_text'] ?? '';
        $translated_text = $_POST['translated_text'] ?? '';
        
        $stmt = $db->prepare("INSERT INTO translations (title, type, original_text, translated_text, created_at) VALUES (?, ?, ?, ?, datetime('now'))");
        if ($stmt->execute([$title, $type, $original_text, $translated_text])) {
            $message = 'Translation added successfully!';
            $messageType = 'success';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $original_text = $_POST['original_text'] ?? '';
        $translated_text = $_POST['translated_text'] ?? '';
        
        $stmt = $db->prepare("UPDATE translations SET title=?, type=?, original_text=?, translated_text=? WHERE id=?");
        if ($stmt->execute([$title, $type, $original_text, $translated_text, $id])) {
            $message = 'Translation updated successfully!';
            $messageType = 'success';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM translations WHERE id=?");
        if ($stmt->execute([$id])) {
            $message = 'Translation deleted successfully!';
            $messageType = 'success';
        }
    }
}

$translations = [];
try {
    $stmt = $db->query("SELECT * FROM translations ORDER BY created_at DESC");
    $translations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $translations = [];
}

$editTranslation = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM translations WHERE id=?");
    $stmt->execute([$editId]);
    $editTranslation = $stmt->fetch(PDO::FETCH_ASSOC);
}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Translations - Runyakitara Hub Admin</title>
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
        .translation-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }
        .translation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .translation-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .translation-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }
        .type-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .type-song {
            background: rgba(240, 147, 251, 0.1);
            color: #f5576c;
        }
        .type-story {
            background: rgba(79, 172, 254, 0.1);
            color: #00f2fe;
        }
        .type-poem {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary);
        }
        .type-document {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        .translation-section {
            margin-bottom: 20px;
        }
        .translation-section h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .translation-text {
            padding: 16px;
            background: var(--light);
            border-radius: 10px;
            font-size: 15px;
            line-height: 1.8;
            color: var(--text);
            white-space: pre-wrap;
        }
        .translation-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid var(--border);
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
            font-family: monospace;
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
                        <i class="bi bi-<?php echo $editTranslation ? 'pencil' : 'plus-circle'; ?>"></i>
                        <?php echo $editTranslation ? 'Edit Translation' : 'Add New Translation'; ?>
                    </h2>
                    <form method="POST" data-validate="true">
                        <input type="hidden" name="action" value="<?php echo $editTranslation ? 'edit' : 'add'; ?>">
                        <?php if ($editTranslation): ?>
                            <input type="hidden" name="id" value="<?php echo $editTranslation['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" id="title" name="title" required 
                                       value="<?php echo $editTranslation ? htmlspecialchars($editTranslation['title']) : ''; ?>"
                                       placeholder="e.g., Traditional Wedding Song">
                            </div>
                            
                            <div class="form-group">
                                <label for="type">Type *</label>
                                <select id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="song" <?php echo ($editTranslation && $editTranslation['type'] === 'song') ? 'selected' : ''; ?>>Song</option>
                                    <option value="story" <?php echo ($editTranslation && $editTranslation['type'] === 'story') ? 'selected' : ''; ?>>Story</option>
                                    <option value="poem" <?php echo ($editTranslation && $editTranslation['type'] === 'poem') ? 'selected' : ''; ?>>Poem</option>
                                    <option value="document" <?php echo ($editTranslation && $editTranslation['type'] === 'document') ? 'selected' : ''; ?>>Document</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="original_text">Original Text (Runyakitara) *</label>
                            <textarea id="original_text" name="original_text" required 
                                      placeholder="Enter the original text in Runyakitara..."><?php echo $editTranslation ? htmlspecialchars($editTranslation['original_text']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="translated_text">English Translation *</label>
                            <textarea id="translated_text" name="translated_text" required 
                                      placeholder="Enter the English translation..."><?php echo $editTranslation ? htmlspecialchars($editTranslation['translated_text']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-<?php echo $editTranslation ? 'check' : 'plus'; ?>-circle"></i>
                                <?php echo $editTranslation ? 'Update Translation' : 'Add Translation'; ?>
                            </button>
                            <?php if ($editTranslation): ?>
                                <a href="translations-manage.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <div class="content-table">
                    <h2 style="margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-arrow-left-right"></i>
                        Translations (<?php echo count($translations); ?>)
                    </h2>
                    
                    <?php if (empty($translations)): ?>
                        <div style="text-align: center; padding: 60px; color: var(--text-light);">
                            <i class="bi bi-arrow-left-right" style="font-size: 64px; opacity: 0.5; display: block; margin-bottom: 16px;"></i>
                            <h3 style="margin-bottom: 8px;">No translations yet</h3>
                            <p>Add your first translation above!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($translations as $trans): ?>
                            <div class="translation-card">
                                <div class="translation-header">
                                    <div class="translation-title"><?php echo htmlspecialchars($trans['title']); ?></div>
                                    <span class="type-badge type-<?php echo $trans['type']; ?>">
                                        <?php echo ucfirst($trans['type']); ?>
                                    </span>
                                </div>
                                
                                <div class="translation-section">
                                    <h4>Original (Runyakitara)</h4>
                                    <div class="translation-text"><?php echo htmlspecialchars($trans['original_text']); ?></div>
                                </div>
                                
                                <div class="translation-section">
                                    <h4>Translation (English)</h4>
                                    <div class="translation-text"><?php echo htmlspecialchars($trans['translated_text']); ?></div>
                                </div>
                                
                                <div class="translation-footer">
                                    <span style="font-size: 13px; color: var(--text-light);">
                                        <i class="bi bi-clock"></i>
                                        <?php echo date('M d, Y', strtotime($trans['created_at'])); ?>
                                    </span>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $trans['id']; ?>" class="btn-icon btn-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this translation?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $trans['id']; ?>">
                                            <button type="submit" class="btn-icon btn-delete" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
