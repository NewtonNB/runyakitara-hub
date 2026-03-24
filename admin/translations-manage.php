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
        softDelete($db, 'translations', $id);
        $message = 'Translation moved to trash.';
        $messageType = 'success';
    } elseif ($action === 'restore') {
        $id = $_POST['id'] ?? '';
        restoreRecord($db, 'translations', $id);
        $message = 'Translation restored.';
        $messageType = 'success';
    } elseif ($action === 'hard_delete') {
        $id = $_POST['id'] ?? '';
        hardDelete($db, 'translations', $id);
        $message = 'Translation permanently deleted.';
        $messageType = 'success';
    }
}

$translations = [];
try {
    if ($showTrash) {
        $translations = $db->query("SELECT * FROM translations WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $translations = $db->query("SELECT * FROM translations WHERE deleted_at IS NULL ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { $translations = []; }

$trashCount = 0;
try { $trashCount = $db->query("SELECT COUNT(*) FROM translations WHERE deleted_at IS NOT NULL")->fetchColumn(); } catch (Exception $e) {}

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
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/modals.css">
    <link rel="stylesheet" href="css/form-validation.css">
    <link rel="stylesheet" href="css/table-utils.css">
    <style>
        .translation-card { background:white; border-radius:16px; padding:24px; margin-bottom:16px; box-shadow:0 1px 3px rgba(0,0,0,0.06),0 4px 12px rgba(0,0,0,0.04); border:1px solid rgba(226,232,240,0.8); transition:all 0.2s ease; }
        .translation-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,0.1); }
        .translation-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; }
        .translation-title { font-size:18px; font-weight:700; color:var(--dark); }
        .type-song    { background:rgba(168,85,247,0.1); color:#a855f7; }
        .type-story   { background:rgba(14,165,233,0.1); color:#0ea5e9; }
        .type-poem    { background:rgba(102,126,234,0.1); color:var(--primary); }
        .type-document{ background:rgba(245,158,11,0.1); color:#d97706; }
        .translation-section { margin-bottom:20px; }
        .translation-section h4 { font-size:11px; font-weight:700; color:var(--text-light); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.8px; }
        .translation-text { padding:14px 16px; background:var(--light); border-radius:10px; font-size:14px; line-height:1.8; color:var(--text); white-space:pre-wrap; }
        .translation-footer { display:flex; justify-content:space-between; align-items:center; padding-top:14px; border-top:1px solid var(--border); }
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

            <div class="content-table">
                <div class="table-header">
                    <h2><i class="bi bi-arrow-left-right"></i> <?php echo $showTrash ? 'Trash' : 'Translations'; ?> (<?php echo count($translations); ?>)</h2>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <?php if ($showTrash): ?>
                            <a href="translations-manage.php" class="btn-add" style="background:var(--text-light);"><i class="bi bi-arrow-left"></i> Back</a>
                        <?php else: ?>
                            <?php if ($trashCount > 0): ?>
                                <a href="?trash=1" class="btn-add" style="background:rgba(239,68,68,0.1);color:var(--danger);box-shadow:none;"><i class="bi bi-trash"></i> Trash (<?php echo $trashCount; ?>)</a>
                            <?php endif; ?>
                            <button class="btn-add" onclick="openAddModal()"><i class="bi bi-plus-circle"></i> Add Translation</button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($translations)): ?>
                    <div class="empty-state">
                        <i class="bi bi-arrow-left-right"></i>
                        <h3>No translations yet</h3>
                        <p>Add your first translation above!</p>
                    </div>
                <?php else: ?>
                    <div id="translationsContainer" style="padding:24px;">
                        <?php foreach ($translations as $trans): ?>
                            <div class="translation-card">
                                <div class="translation-header">
                                    <div class="translation-title"><?php echo htmlspecialchars($trans['title']); ?></div>
                                    <span class="type-badge type-<?php echo $trans['type']; ?>"><?php echo ucfirst($trans['type']); ?></span>
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
                                    <span style="font-size:13px;color:var(--text-light);"><i class="bi bi-clock"></i> <?php echo date('M d, Y', strtotime($trans['created_at'])); ?></span>
                                    <div class="action-buttons">
                                        <?php if ($showTrash): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="id" value="<?php echo $trans['id']; ?>">
                                                <button type="submit" class="btn-icon btn-view" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                            </form>
                                            <button class="btn-delete-modal" onclick="openHardDeleteModal(<?php echo $trans['id']; ?>, '<?php echo htmlspecialchars($trans['title'], ENT_QUOTES); ?>')" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                                        <?php else: ?>
                                            <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode($trans); ?>)' title="Edit"><i class="bi bi-pencil"></i></button>
                                            <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $trans['id']; ?>, '<?php echo htmlspecialchars($trans['title'], ENT_QUOTES); ?>')" title="Move to Trash"><i class="bi bi-trash"></i></button>
                                        <?php endif; ?>
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

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="transModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <h2><i class="bi bi-arrow-left-right"></i> <span id="modalTitle">Add Translation</span></h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" id="transForm" data-validate="true">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="transId">

                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Title</label>
                        <input type="text" name="title" id="modalTransTitle" required placeholder="e.g., Traditional Wedding Song">
                    </div>
                    <div class="form-group">
                        <label class="required">Type</label>
                        <select name="type" id="modalType" required>
                            <option value="">Select Type</option>
                            <option value="song">Song</option>
                            <option value="story">Story</option>
                            <option value="poem">Poem</option>
                            <option value="document">Document</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="required">Original Text (Runyakitara)</label>
                    <textarea name="original_text" id="modalOriginal" required placeholder="Enter the original text in Runyakitara..."></textarea>
                </div>

                <div class="form-group">
                    <label class="required">English Translation</label>
                    <textarea name="translated_text" id="modalTranslated" required placeholder="Enter the English translation..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()"><i class="bi bi-x-circle"></i> Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary"><i class="bi bi-check-circle"></i> <span id="submitBtnText">Add Translation</span></button>
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
            <p id="deleteMessage">This translation will be moved to trash.</p>
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
    document.getElementById('modalTitle').textContent = 'Add Translation';
    document.getElementById('submitBtnText').textContent = 'Add Translation';
    document.getElementById('formAction').value = 'add';
    document.getElementById('transForm').reset();
    document.getElementById('transId').value = '';
    resetFormValidation('transForm');
    document.getElementById('transModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function openEditModal(trans) {
    document.getElementById('modalTitle').textContent = 'Edit Translation';
    document.getElementById('submitBtnText').textContent = 'Update Translation';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('transId').value = trans.id;
    document.getElementById('modalTransTitle').value = trans.title;
    document.getElementById('modalType').value = trans.type;
    document.getElementById('modalOriginal').value = trans.original_text;
    document.getElementById('modalTranslated').value = trans.translated_text;
    resetFormValidation('transForm');
    document.getElementById('transModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeModal() {
    document.getElementById('transModal').classList.remove('active');
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

document.getElementById('transModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });
document.getElementById('deleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeDeleteModal(); });
document.getElementById('hardDeleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeHardDeleteModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeDeleteModal(); closeHardDeleteModal(); } });
document.getElementById('mobileToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('active'));
</script>
<script src="js/form-validation.js"></script>
<script src="js/table-utils.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    TableUtils.initCards({ containerId: 'translationsContainer', cardSelector: '.translation-card', rowsPerPage: 6, exportName: 'Translations' });
});
</script>
</body>
</html>
