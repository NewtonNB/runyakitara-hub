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

$message = '';
$messageType = '';
$showTrash = isset($_GET['trash']);

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
        softDelete($db, 'grammar_topics', $id);
        $message = 'Topic moved to trash.';
        $messageType = 'success';
    } elseif ($action === 'restore') {
        $id = $_POST['id'] ?? '';
        restoreRecord($db, 'grammar_topics', $id);
        $message = 'Topic restored.';
        $messageType = 'success';
    } elseif ($action === 'hard_delete') {
        $id = $_POST['id'] ?? '';
        hardDelete($db, 'grammar_topics', $id);
        $message = 'Topic permanently deleted.';
        $messageType = 'success';
    }
}

$topics = [];
try {
    if ($showTrash) {
        $topics = $db->query("SELECT * FROM grammar_topics WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $topics = $db->query("SELECT * FROM grammar_topics WHERE deleted_at IS NULL ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { $topics = []; }

$trashCount = 0;
try { $trashCount = $db->query("SELECT COUNT(*) FROM grammar_topics WHERE deleted_at IS NOT NULL")->fetchColumn(); } catch (Exception $e) {}

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

            <div class="content-table">
                <div class="table-header">
                    <h2><i class="bi bi-pencil-square"></i> <?php echo $showTrash ? 'Trash' : 'Grammar Topics'; ?> (<?php echo count($topics); ?>)</h2>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <?php if ($showTrash): ?>
                            <a href="grammar-manage.php" class="btn-add" style="background:var(--text-light);"><i class="bi bi-arrow-left"></i> Back</a>
                        <?php else: ?>
                            <?php if ($trashCount > 0): ?>
                                <a href="?trash=1" class="btn-add" style="background:rgba(239,68,68,0.1);color:var(--danger);box-shadow:none;"><i class="bi bi-trash"></i> Trash (<?php echo $trashCount; ?>)</a>
                            <?php endif; ?>
                            <button class="btn-add" onclick="openAddModal()"><i class="bi bi-plus-circle"></i> Add Topic</button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($topics)): ?>
                    <div class="empty-state">
                        <i class="bi bi-pencil-square"></i>
                        <h3>No grammar topics yet</h3>
                        <p>Add your first topic above!</p>
                    </div>
                <?php else: ?>
                    <div style="padding:24px;">
                        <div id="grammarContainer" class="grammar-grid">
                            <?php foreach ($topics as $topic): ?>
                                <div class="grammar-card">
                                    <div class="grammar-title"><?php echo htmlspecialchars($topic['title']); ?></div>
                                    <div class="grammar-content"><?php echo htmlspecialchars($topic['content']); ?></div>
                                    <div class="grammar-footer">
                                        <span class="difficulty-badge difficulty-<?php echo $topic['difficulty']; ?>"><?php echo ucfirst($topic['difficulty']); ?></span>
                                        <div class="action-buttons">
                                            <?php if ($showTrash): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="restore">
                                                    <input type="hidden" name="id" value="<?php echo $topic['id']; ?>">
                                                    <button type="submit" class="btn-icon btn-view" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                </form>
                                                <button class="btn-delete-modal" onclick="openHardDeleteModal(<?php echo $topic['id']; ?>, '<?php echo htmlspecialchars($topic['title'], ENT_QUOTES); ?>')" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                                            <?php else: ?>
                                                <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode($topic); ?>)' title="Edit"><i class="bi bi-pencil"></i></button>
                                                <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $topic['id']; ?>, '<?php echo htmlspecialchars($topic['title'], ENT_QUOTES); ?>')" title="Move to Trash"><i class="bi bi-trash"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="grammarModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="bi bi-pencil-square"></i> <span id="modalTitle">Add Grammar Topic</span></h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" id="grammarForm" data-validate="true">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="grammarId">

                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Topic Title</label>
                        <input type="text" name="title" id="modalGrammarTitle" required placeholder="e.g., Noun Classes in Runyakitara">
                    </div>
                    <div class="form-group">
                        <label class="required">Difficulty</label>
                        <select name="difficulty" id="modalDifficulty" required>
                            <option value="">Select Difficulty</option>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="required">Explanation</label>
                    <textarea name="content" id="modalGrammarContent" required placeholder="Explain the grammar topic..."></textarea>
                </div>

                <div class="form-group">
                    <label class="required">Examples</label>
                    <textarea name="examples" id="modalExamples" required placeholder="Provide examples..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()"><i class="bi bi-x-circle"></i> Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary"><i class="bi bi-check-circle"></i> <span id="submitBtnText">Add Topic</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-container modal-sm">
        <div class="modal-body modal-confirm">
            <div class="modal-confirm-icon warning"><i class="bi bi-trash"></i></div>
            <h3>Move to Trash?</h3>
            <p id="deleteMessage">This topic will be moved to trash.</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <div style="display:flex;gap:12px;justify-content:center;">
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
    document.getElementById('modalTitle').textContent = 'Add Grammar Topic';
    document.getElementById('submitBtnText').textContent = 'Add Topic';
    document.getElementById('formAction').value = 'add';
    document.getElementById('grammarForm').reset();
    document.getElementById('grammarId').value = '';
    resetFormValidation('grammarForm');
    document.getElementById('grammarModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function openEditModal(topic) {
    document.getElementById('modalTitle').textContent = 'Edit Grammar Topic';
    document.getElementById('submitBtnText').textContent = 'Update Topic';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('grammarId').value = topic.id;
    document.getElementById('modalGrammarTitle').value = topic.title;
    document.getElementById('modalDifficulty').value = topic.difficulty;
    document.getElementById('modalGrammarContent').value = topic.content;
    document.getElementById('modalExamples').value = topic.examples || '';
    resetFormValidation('grammarForm');
    document.getElementById('grammarModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeModal() {
    document.getElementById('grammarModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

function openDeleteModal(id, title) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteMessage').innerHTML = `"<strong>${title}</strong>" will be moved to trash.`;
    document.getElementById('deleteModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

function openHardDeleteModal(id, title) {
    document.getElementById('hardDeleteId').value = id;
    document.getElementById('hardDeleteMessage').innerHTML = `Permanently delete "<strong>${title}</strong>"? This cannot be undone.`;
    document.getElementById('hardDeleteModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeHardDeleteModal() {
    document.getElementById('hardDeleteModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

document.getElementById('grammarModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });
document.getElementById('deleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeDeleteModal(); });
document.getElementById('hardDeleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeHardDeleteModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeDeleteModal(); closeHardDeleteModal(); } });
document.getElementById('mobileToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('active'));
</script>
<script src="js/form-validation.js"></script>
<script src="js/table-utils.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    TableUtils.initCards({ containerId: 'grammarContainer', cardSelector: '.grammar-card', rowsPerPage: 9, exportName: 'Grammar_Topics' });
});
</script>
</body>
</html>
