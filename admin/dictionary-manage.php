<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once 'includes/soft-delete.php';
$db = getDBConnection();
ensureSoftDelete($db);

// Handle actions
$message = '';
$messageType = '';
$showTrash = isset($_GET['trash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $word = $_POST['word'] ?? '';
        $translation = $_POST['translation'] ?? '';
        $pronunciation = $_POST['pronunciation'] ?? '';
        $example = $_POST['example'] ?? '';
        $category = $_POST['category'] ?? '';
        
        $stmt = $db->prepare("INSERT INTO dictionary (word_runyakitara, word_english, pronunciation, example_sentence, category, created_at) VALUES (?, ?, ?, ?, ?, datetime('now'))");
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
        
        $stmt = $db->prepare("UPDATE dictionary SET word_runyakitara=?, word_english=?, pronunciation=?, example_sentence=?, category=? WHERE id=?");
        if ($stmt->execute([$word, $translation, $pronunciation, $example, $category, $id])) {
            $message = 'Word updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating word.';
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (softDelete($db, 'dictionary', $id)) {
            $message = 'Word moved to trash.';
            $messageType = 'success';
        }
    } elseif ($action === 'restore') {
        $id = $_POST['id'] ?? '';
        restoreRecord($db, 'dictionary', $id);
        $message = 'Word restored.';
        $messageType = 'success';
    } elseif ($action === 'hard_delete') {
        $id = $_POST['id'] ?? '';
        hardDelete($db, 'dictionary', $id);
        $message = 'Word permanently deleted.';
        $messageType = 'success';
    }
}

// Get all words
$words = [];
try {
    if ($showTrash) {
        $words = $db->query("SELECT * FROM dictionary WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $words = $db->query("SELECT * FROM dictionary WHERE deleted_at IS NULL ORDER BY word_runyakitara ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { $words = []; }

$trashCount = 0;
try { $trashCount = $db->query("SELECT COUNT(*) FROM dictionary WHERE deleted_at IS NOT NULL")->fetchColumn(); } catch (Exception $e) {}
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
    <link rel="stylesheet" href="css/admin-responsive.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/modals.css">
    <link rel="stylesheet" href="css/form-validation.css">
    <link rel="stylesheet" href="css/table-utils.css">
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
                        <h2><i class="bi bi-journal-text"></i> <?php echo $showTrash ? 'Trash' : 'Dictionary'; ?> (<?php echo count($words); ?> words)</h2>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <div class="search-box">
                                <input type="text" id="searchInput" placeholder="Search words..." onkeyup="searchTable()">
                                <i class="bi bi-search"></i>
                            </div>
                            <?php if ($showTrash): ?>
                                <a href="dictionary-manage.php" class="btn-add" style="background:var(--text-light);"><i class="bi bi-arrow-left"></i> Back</a>
                            <?php else: ?>
                                <?php if ($trashCount > 0): ?>
                                    <a href="?trash=1" class="btn-add" style="background:rgba(239,68,68,0.1);color:var(--danger);box-shadow:none;">
                                        <i class="bi bi-trash"></i> Trash (<?php echo $trashCount; ?>)
                                    </a>
                                <?php endif; ?>
                                <button class="btn-add" onclick="openAddModal()">
                                    <i class="bi bi-plus-circle"></i> Add Word
                                </button>
                            <?php endif; ?>
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
                                            <strong><?php echo htmlspecialchars($word['word_runyakitara']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($word['word_english']); ?></td>
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
                                                <?php if ($showTrash): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="restore">
                                                        <input type="hidden" name="id" value="<?php echo $word['id']; ?>">
                                                        <button type="submit" class="btn-icon btn-view" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                    </form>
                                                    <button class="btn-delete-modal" onclick="openHardDeleteModal(<?php echo $word['id']; ?>, '<?php echo htmlspecialchars($word['word_runyakitara'], ENT_QUOTES); ?>')" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                                                <?php else: ?>
                                                    <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode($word); ?>)' title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $word['id']; ?>, '<?php echo htmlspecialchars($word['word_runyakitara'], ENT_QUOTES); ?>')" title="Move to Trash">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
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
                <div class="modal-confirm-icon warning">
                    <i class="bi bi-trash"></i>
                </div>
                <h3>Move to Trash?</h3>
                <p id="deleteMessage">This word will be moved to trash and can be restored later.</p>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <button type="button" class="modal-btn modal-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                        <button type="submit" class="modal-btn modal-btn-danger"><i class="bi bi-trash"></i> Move to Trash</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hard Delete Modal -->
    <div class="modal-overlay" id="hardDeleteModal">
        <div class="modal-container modal-sm">
            <div class="modal-body modal-confirm">
                <div class="modal-confirm-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
                <h3>Delete Permanently?</h3>
                <p id="hardDeleteMessage">This cannot be undone.</p>
                <form method="POST" id="hardDeleteForm">
                    <input type="hidden" name="action" value="hard_delete">
                    <input type="hidden" name="id" id="hardDeleteId">
                    <div style="display:flex;gap:12px;justify-content:center;">
                        <button type="button" class="modal-btn modal-btn-secondary" onclick="closeHardDeleteModal()">Cancel</button>
                        <button type="submit" class="modal-btn modal-btn-danger"><i class="bi bi-trash"></i> Delete Forever</button>
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
            resetFormValidation('wordForm');
            document.getElementById('wordModal').classList.add('active');
        }
        
        function openEditModal(word) {
            document.getElementById('modalTitle').textContent = 'Edit Word';
            document.getElementById('submitBtnText').textContent = 'Update Word';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('wordId').value = word.id;
            document.getElementById('modalWord').value = word.word_runyakitara;
            document.getElementById('modalTranslation').value = word.word_english;
            document.getElementById('modalPronunciation').value = word.pronunciation || '';
            document.getElementById('modalCategory').value = word.category || '';
            document.getElementById('modalExample').value = word.example_sentence || '';
            resetFormValidation('wordForm');
            document.getElementById('wordModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('wordModal').classList.remove('active');
        }
        
        function openDeleteModal(id, word) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteMessage').innerHTML = `"<strong>${word}</strong>" will be moved to trash.`;
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        function openHardDeleteModal(id, word) {
            document.getElementById('hardDeleteId').value = id;
            document.getElementById('hardDeleteMessage').innerHTML = `Permanently delete "<strong>${word}</strong>"? This cannot be undone.`;
            document.getElementById('hardDeleteModal').classList.add('active');
        }

        function closeHardDeleteModal() {
            document.getElementById('hardDeleteModal').classList.remove('active');
        }
        
        // Close modal on overlay click
        document.getElementById('wordModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        
        document.getElementById('deleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        document.getElementById('hardDeleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeHardDeleteModal();
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDeleteModal();
                closeHardDeleteModal();
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
    <script src="js/table-utils.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        TableUtils.init({ tableId: 'dictionaryTable', rowsPerPage: 10, exportName: 'Dictionary' });
    });
    </script>
</body>
</html>
