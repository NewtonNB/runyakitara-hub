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
        $proverb = $_POST['proverb'] ?? '';
        $translation = $_POST['translation'] ?? '';
        $meaning = $_POST['meaning'] ?? '';
        $usage = $_POST['usage'] ?? '';
        
        $stmt = $db->prepare("INSERT INTO proverbs (proverb, translation, meaning, usage, created_at) VALUES (?, ?, ?, ?, datetime('now'))");
        if ($stmt->execute([$proverb, $translation, $meaning, $usage])) {
            $message = 'Proverb added successfully!';
            $messageType = 'success';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $proverb = $_POST['proverb'] ?? '';
        $translation = $_POST['translation'] ?? '';
        $meaning = $_POST['meaning'] ?? '';
        $usage = $_POST['usage'] ?? '';
        
        $stmt = $db->prepare("UPDATE proverbs SET proverb=?, translation=?, meaning=?, usage=? WHERE id=?");
        if ($stmt->execute([$proverb, $translation, $meaning, $usage, $id])) {
            $message = 'Proverb updated successfully!';
            $messageType = 'success';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM proverbs WHERE id=?");
        if ($stmt->execute([$id])) {
            $message = 'Proverb deleted successfully!';
            $messageType = 'success';
        }
    }
}

$proverbs = [];
try {
    $stmt = $db->query("SELECT * FROM proverbs ORDER BY created_at DESC");
    $proverbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $proverbs = [];
}

$editProverb = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM proverbs WHERE id=?");
    $stmt->execute([$editId]);
    $editProverb = $stmt->fetch(PDO::FETCH_ASSOC);
}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Proverbs - Runyakitara Hub Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/form-validation.css">
    <style>
        .proverb-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }
        .proverb-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .proverb-text {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 12px;
            font-style: italic;
        }
        .proverb-translation {
            font-size: 16px;
            color: var(--text);
            margin-bottom: 16px;
            padding-left: 20px;
            border-left: 3px solid var(--border);
        }
        .proverb-meaning {
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 12px;
        }
        .proverb-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }
        .form-section, .content-table {
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
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-group textarea {
            min-height: 100px;
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
                        <i class="bi bi-<?php echo $editProverb ? 'pencil' : 'plus-circle'; ?>"></i>
                        <?php echo $editProverb ? 'Edit Proverb' : 'Add New Proverb'; ?>
                    </h2>
                    <form method="POST" data-validate="true">
                        <input type="hidden" name="action" value="<?php echo $editProverb ? 'edit' : 'add'; ?>">
                        <?php if ($editProverb): ?>
                            <input type="hidden" name="id" value="<?php echo $editProverb['id'] ?? ''; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="proverb">Proverb (Runyakitara) *</label>
                            <input type="text" id="proverb" name="proverb" required 
                                   value="<?php echo $editProverb ? htmlspecialchars($editProverb['proverb'] ?? '') : ''; ?>"
                                   placeholder="e.g., Akanyonyi kazooba karaara mu kiti">
                        </div>
                        
                        <div class="form-group">
                            <label for="translation">English Translation *</label>
                            <input type="text" id="translation" name="translation" required 
                                   value="<?php echo $editProverb ? htmlspecialchars($editProverb['translation'] ?? '') : ''; ?>"
                                   placeholder="e.g., A bird that will fly sits on a tree">
                        </div>
                        
                        <div class="form-group">
                            <label for="meaning">Meaning/Explanation *</label>
                            <textarea id="meaning" name="meaning" required 
                                      placeholder="Explain what this proverb means..."><?php echo $editProverb ? htmlspecialchars($editProverb['meaning'] ?? '') : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="usage">Usage Context (Optional)</label>
                            <textarea id="usage" name="usage" 
                                      placeholder="When and how is this proverb used?"><?php echo $editProverb ? htmlspecialchars($editProverb['usage'] ?? '') : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-<?php echo $editProverb ? 'check' : 'plus'; ?>-circle"></i>
                                <?php echo $editProverb ? 'Update Proverb' : 'Add Proverb'; ?>
                            </button>
                            <?php if ($editProverb): ?>
                                <a href="proverbs-manage.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <div class="content-table">
                    <h2 style="margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-chat-quote"></i>
                        All Proverbs (<?php echo count($proverbs); ?>)
                    </h2>
                    
                    <?php if (empty($proverbs)): ?>
                        <div style="text-align: center; padding: 60px; color: var(--text-light);">
                            <i class="bi bi-chat-quote" style="font-size: 64px; opacity: 0.5; display: block; margin-bottom: 16px;"></i>
                            <h3 style="margin-bottom: 8px;">No proverbs yet</h3>
                            <p>Add your first proverb above!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($proverbs as $prov): ?>
                            <div class="proverb-card">
                                <div class="proverb-text">
                                    "<?php echo htmlspecialchars($prov['proverb'] ?? ''); ?>"
                                </div>
                                <div class="proverb-translation">
                                    <?php echo htmlspecialchars($prov['translation'] ?? ''); ?>
                                </div>
                                <div class="proverb-meaning">
                                    <strong>Meaning:</strong> <?php echo htmlspecialchars($prov['meaning'] ?? ''); ?>
                                </div>
                                <?php if (!empty($prov['usage'])): ?>
                                    <div class="proverb-meaning">
                                        <strong>Usage:</strong> <?php echo htmlspecialchars($prov['usage']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="proverb-footer">
                                    <span style="font-size: 13px; color: var(--text-light);">
                                        <i class="bi bi-clock"></i>
                                        <?php echo date('M d, Y', strtotime($prov['created_at'])); ?>
                                    </span>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $prov['id']; ?>" class="btn-icon btn-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this proverb?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $prov['id']; ?>">
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
