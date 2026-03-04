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
        $word = $_POST['word'] ?? '';
        $translation = $_POST['translation'] ?? '';
        $pronunciation = $_POST['pronunciation'] ?? '';
        $example = $_POST['example'] ?? '';
        $category = $_POST['category'] ?? '';
        
        $stmt = $db->prepare("INSERT INTO dictionary (word, translation, pronunciation, example, category, created_at) VALUES (?, ?, ?, ?, ?, datetime('now'))");
        if ($stmt->execute([$word, $translation, $pronunciation, $example, $category])) {
            $message = 'Word added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error adding word.';
            $messageType = 'error';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $word = $_POST['word'] ?? '';
        $translation = $_POST['translation'] ?? '';
        $pronunciation = $_POST['pronunciation'] ?? '';
        $example = $_POST['example'] ?? '';
        $category = $_POST['category'] ?? '';
        
        $stmt = $db->prepare("UPDATE dictionary SET word=?, translation=?, pronunciation=?, example=?, category=? WHERE id=?");
        if ($stmt->execute([$word, $translation, $pronunciation, $example, $category, $id])) {
            $message = 'Word updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating word.';
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM dictionary WHERE id=?");
        if ($stmt->execute([$id])) {
            $message = 'Word deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting word.';
            $messageType = 'error';
        }
    }
}

// Get all words
$words = [];
try {
    $stmt = $db->query("SELECT * FROM dictionary ORDER BY word ASC");
    $words = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $words = [];
}

// Get word for editing
$editWord = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM dictionary WHERE id=?");
    $stmt->execute([$editId]);
    $editWord = $stmt->fetch(PDO::FETCH_ASSOC);
}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Dictionary - Runyakitara Hub Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/form-validation.css">
    <link rel="stylesheet" href="css/modals.css">
    
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
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            padding: 10px 40px 10px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            width: 300px;
        }
        
        .search-box i {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
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
        
        .category-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary);
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
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .pronunciation {
            color: var(--text-light);
            font-style: italic;
            font-size: 14px;
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
                
                <!-- Dictionary Table -->
                <div class="content-table">
                    <div class="table-header">
                        <h2><i class="bi bi-journal-text"></i> Dictionary (<?php echo count($words); ?> words)</h2>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <div class="search-box">
                                <input type="text" id="searchInput" placeholder="Search words..." onkeyup="searchTable()">
                                <i class="bi bi-search"></i>
                            </div>
                            <button class="btn-add" onclick="openAddModal()">
                                <i class="bi bi-plus-circle"></i>
                                Add Word
                            </button>
                        </div>
                    </div>
                    <table id="dictionaryTable">
                        <thead>
                            <tr>
                                <th>Word</th>
                                <th>Translation</th>
                                <th>Pronunciation</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($words)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-light);">
                                        <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 12px; opacity: 0.5;"></i>
                                        No words found. Add your first word above!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($words as $word): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($word['word']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($word['translation']); ?></td>
                                        <td>
                                            <?php if ($word['pronunciation']): ?>
                                                <span class="pronunciation"><?php echo htmlspecialchars($word['pronunciation']); ?></span>
                                            <?php else: ?>
                                                <span style="color: var(--text-light);">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($word['category']): ?>
                                                <span class="category-badge"><?php echo ucfirst($word['category']); ?></span>
                                            <?php else: ?>
                                                <span style="color: var(--text-light);">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode($word); ?>)' title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $word['id']; ?>, '<?php echo htmlspecialchars($word['word'], ENT_QUOTES); ?>')" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
    
    <!-- Add/Edit Word Modal -->
    <div class="modal-overlay" id="wordModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2><i class="bi bi-journal-text"></i> <span id="modalTitle">Add Word</span></h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" id="wordForm" data-validate="true">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="wordId">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="modalWord" class="required">Runyakitara Word</label>
                            <input type="text" id="modalWord" name="word" required minlength="2" maxlength="100"
                                   placeholder="e.g., Oraire">
                        </div>
                        
                        <div class="form-group">
                            <label for="modalTranslation" class="required">English Translation</label>
                            <input type="text" id="modalTranslation" name="translation" required minlength="2" maxlength="100"
                                   placeholder="e.g., Good morning">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="modalPronunciation">Pronunciation</label>
                            <input type="text" id="modalPronunciation" name="pronunciation" maxlength="100"
                                   placeholder="e.g., oh-rye-ray">
                        </div>
                        
                        <div class="form-group">
                            <label for="modalCategory">Category</label>
                            <select id="modalCategory" name="category">
                                <option value="">Select Category</option>
                                <option value="greetings">Greetings</option>
                                <option value="numbers">Numbers</option>
                                <option value="family">Family</option>
                                <option value="food">Food</option>
                                <option value="animals">Animals</option>
                                <option value="verbs">Verbs</option>
                                <option value="adjectives">Adjectives</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="modalExample">Example Sentence</label>
                        <textarea id="modalExample" name="example" 
                                  placeholder="Enter an example sentence using this word..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="modal-btn modal-btn-primary">
                        <i class="bi bi-check-circle"></i> <span id="submitBtnText">Add Word</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-container modal-sm">
            <div class="modal-body modal-confirm">
                <div class="modal-confirm-icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h3>Delete Word?</h3>
                <p id="deleteMessage">Are you sure you want to delete this word?</p>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <button type="button" class="modal-btn modal-btn-secondary" onclick="closeDeleteModal()">
                            Cancel
                        </button>
                        <button type="submit" class="modal-btn modal-btn-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Word';
            document.getElementById('submitBtnText').textContent = 'Add Word';
            document.getElementById('formAction').value = 'add';
            document.getElementById('wordForm').reset();
            document.getElementById('wordId').value = '';
            document.getElementById('wordModal').classList.add('active');
        }
        
        function openEditModal(word) {
            document.getElementById('modalTitle').textContent = 'Edit Word';
            document.getElementById('submitBtnText').textContent = 'Update Word';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('wordId').value = word.id;
            document.getElementById('modalWord').value = word.word;
            document.getElementById('modalTranslation').value = word.translation;
            document.getElementById('modalPronunciation').value = word.pronunciation || '';
            document.getElementById('modalCategory').value = word.category || '';
            document.getElementById('modalExample').value = word.example || '';
            document.getElementById('wordModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('wordModal').classList.remove('active');
        }
        
        function openDeleteModal(id, word) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete "<strong>${word}</strong>"?`;
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }
        
        // Close modal on overlay click
        document.getElementById('wordModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        
        document.getElementById('deleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDeleteModal();
            }
        });
        
        document.getElementById('mobileToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('dictionaryTable');
            const tr = table.getElementsByTagName('tr');
            
            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length - 1; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }
    </script>
    <script src="js/form-validation.js"></script>
</body>
</html>
