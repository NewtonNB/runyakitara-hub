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
        $proverb = $_POST['proverb'] ?? '';
        $translation = $_POST['translation'] ?? '';
        $meaning = $_POST['meaning'] ?? '';
        $usage = $_POST['usage'] ?? '';

        $stmt = $db->prepare("INSERT INTO proverbs (proverb, translation, meaning, `usage`, created_at) VALUES (?, ?, ?, ?, NOW())");
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

        $stmt = $db->prepare("UPDATE proverbs SET proverb=?, translation=?, meaning=?, `usage`=? WHERE id=?");
        if ($stmt->execute([$proverb, $translation, $meaning, $usage, $id])) {
            $message = 'Proverb updated successfully!';
            $messageType = 'success';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        softDelete($db, 'proverbs', $id);
        $message = 'Proverb moved to trash.';
        $messageType = 'success';
    } elseif ($action === 'restore') {
        $id = $_POST['id'] ?? '';
        restoreRecord($db, 'proverbs', $id);
        $message = 'Proverb restored.';
        $messageType = 'success';
    } elseif ($action === 'hard_delete') {
        $id = $_POST['id'] ?? '';
        hardDelete($db, 'proverbs', $id);
        $message = 'Proverb permanently deleted.';
        $messageType = 'success';
    }
}

$proverbs = [];
try {
    if ($showTrash) {
        $proverbs = $db->query("SELECT * FROM proverbs WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $proverbs = $db->query("SELECT * FROM proverbs WHERE deleted_at IS NULL ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { $proverbs = []; }

$trashCount = 0;
try { $trashCount = $db->query("SELECT COUNT(*) FROM proverbs WHERE deleted_at IS NOT NULL")->fetchColumn(); } catch (Exception $e) {}

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
    <link rel="stylesheet" href="css/admin-responsive.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/modals.css">
    <link rel="stylesheet" href="css/form-validation.css">
    <link rel="stylesheet" href="css/table-utils.css">
    <style>
        #proverbsContainer { padding: 24px; }

        .proverbs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .proverb-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid var(--border, #e2e8f0);
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .proverb-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.12);
            border-color: rgba(102,126,234,0.3);
        }

        .proverb-text {
            font-size: 15px;
            font-weight: 700;
            color: var(--dark, #1e293b);
            font-style: italic;
            border-left: 3px solid var(--primary, #667eea);
            padding-left: 12px;
            line-height: 1.5;
        }

        .proverb-translation {
            font-size: 13px;
            color: var(--text, #334155);
            padding-left: 15px;
            line-height: 1.5;
        }

        .proverb-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .proverb-meaning {
            font-size: 12px;
            color: var(--text-light, #64748b);
            line-height: 1.5;
        }

        .proverb-meaning strong {
            color: var(--text, #334155);
            font-weight: 600;
        }

        .proverb-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px solid var(--border, #e2e8f0);
            margin-top: auto;
        }

        @media (max-width: 900px) {
            .proverbs-grid { grid-template-columns: 1fr; }
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

            <div class="content-table">
                <div class="table-header">
                    <h2><i class="bi bi-chat-quote"></i> <?php echo $showTrash ? 'Trash' : 'All Proverbs'; ?> (<?php echo count($proverbs); ?>)</h2>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <?php if ($showTrash): ?>
                            <a href="proverbs-manage.php" class="btn-add" style="background:var(--text-light);"><i class="bi bi-arrow-left"></i> Back</a>
                        <?php else: ?>
                            <?php if ($trashCount > 0): ?>
                                <a href="?trash=1" class="btn-add" style="background:rgba(239,68,68,0.1);color:var(--danger);box-shadow:none;"><i class="bi bi-trash"></i> Trash (<?php echo $trashCount; ?>)</a>
                            <?php endif; ?>
                            <button class="btn-add" onclick="openAddModal()"><i class="bi bi-plus-circle"></i> Add Proverb</button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($proverbs)): ?>
                    <div class="empty-state">
                        <i class="bi bi-chat-quote"></i>
                        <h3>No proverbs yet</h3>
                        <p>Add your first proverb above!</p>
                    </div>
                <?php else: ?>
                    <div id="proverbsContainer" style="padding: 24px;">
                        <div class="proverbs-grid">
                        <?php foreach ($proverbs as $prov): ?>
                            <div class="proverb-card">
                                <div class="proverb-text">"<?php echo htmlspecialchars($prov['proverb'] ?? ''); ?>"</div>
                                <div class="proverb-translation"><?php echo htmlspecialchars($prov['translation'] ?? ''); ?></div>
                                <div class="proverb-meta">
                                    <?php if (!empty($prov['meaning'])): ?>
                                        <div class="proverb-meaning"><strong>Meaning:</strong> <?php echo htmlspecialchars($prov['meaning']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($prov['usage'])): ?>
                                        <div class="proverb-meaning"><strong>Usage:</strong> <?php echo htmlspecialchars($prov['usage']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="proverb-footer">
                                    <span style="font-size:12px;color:var(--text-light);"><i class="bi bi-clock"></i> <?php echo date('M d, Y', strtotime($prov['created_at'])); ?></span>
                                    <div class="action-buttons">
                                        <?php if ($showTrash): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="id" value="<?php echo $prov['id']; ?>">
                                                <button type="submit" class="btn-icon btn-view" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                            </form>
                                            <button class="btn-delete-modal" onclick="openHardDeleteModal(<?php echo $prov['id']; ?>, '<?php echo htmlspecialchars(substr($prov['proverb'] ?? '', 0, 30), ENT_QUOTES); ?>')" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                                        <?php else: ?>
                                            <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode($prov); ?>)' title="Edit"><i class="bi bi-pencil"></i></button>
                                            <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $prov['id']; ?>, '<?php echo htmlspecialchars(substr($prov['proverb'] ?? '', 0, 40), ENT_QUOTES); ?>')" title="Move to Trash"><i class="bi bi-trash"></i></button>
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
<div class="modal-overlay" id="provModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="bi bi-chat-quote"></i> <span id="modalTitle">Add Proverb</span></h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" id="provForm" data-validate="true">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="provId">

                <div class="form-group">
                    <label class="required">Proverb (Runyakitara)</label>
                    <input type="text" name="proverb" id="modalProverb" required placeholder="e.g., Akanyonyi kazooba karaara mu kiti">
                </div>

                <div class="form-group">
                    <label class="required">English Translation</label>
                    <input type="text" name="translation" id="modalTranslation" required placeholder="e.g., A bird that will fly sits on a tree">
                </div>

                <div class="form-group">
                    <label class="required">Meaning / Explanation</label>
                    <textarea name="meaning" id="modalMeaning" required placeholder="Explain what this proverb means..."></textarea>
                </div>

                <div class="form-group">
                    <label>Usage Context (Optional)</label>
                    <textarea name="usage" id="modalUsage" placeholder="When and how is this proverb used?"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()"><i class="bi bi-x-circle"></i> Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary"><i class="bi bi-check-circle"></i> <span id="submitBtnText">Add Proverb</span></button>
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
            <p id="deleteMessage">This proverb will be moved to trash.</p>
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
    document.getElementById('modalTitle').textContent = 'Add Proverb';
    document.getElementById('submitBtnText').textContent = 'Add Proverb';
    document.getElementById('formAction').value = 'add';
    document.getElementById('provForm').reset();
    document.getElementById('provId').value = '';
    resetFormValidation('provForm');
    document.getElementById('provModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function openEditModal(prov) {
    document.getElementById('modalTitle').textContent = 'Edit Proverb';
    document.getElementById('submitBtnText').textContent = 'Update Proverb';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('provId').value = prov.id;
    document.getElementById('modalProverb').value = prov.proverb || '';
    document.getElementById('modalTranslation').value = prov.translation || '';
    document.getElementById('modalMeaning').value = prov.meaning || '';
    document.getElementById('modalUsage').value = prov.usage || '';
    resetFormValidation('provForm');
    document.getElementById('provModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeModal() {
    document.getElementById('provModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

function openDeleteModal(id, text) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteMessage').innerHTML = `"<strong>${text}...</strong>" will be moved to trash.`;
    document.getElementById('deleteModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

function openHardDeleteModal(id, text) {
    document.getElementById('hardDeleteId').value = id;
    document.getElementById('hardDeleteMessage').innerHTML = `Permanently delete "<strong>${text}...</strong>"? This cannot be undone.`;
    document.getElementById('hardDeleteModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeHardDeleteModal() {
    document.getElementById('hardDeleteModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

document.getElementById('provModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });
document.getElementById('deleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeDeleteModal(); });
document.getElementById('hardDeleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeHardDeleteModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeDeleteModal(); closeHardDeleteModal(); } });
document.getElementById('mobileToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('active'));
</script>
<script src="js/form-validation.js"></script>
<script src="js/table-utils.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    TableUtils.initCards({ containerId: 'proverbsContainer', cardSelector: '.proverb-card', rowsPerPage: 9, exportName: 'Proverbs' });
});
</script>
</body>
</html>
