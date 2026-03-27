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
        $level = $_POST['level'] ?? '';
        $content = $_POST['content'] ?? '';
        $vocabulary = $_POST['vocabulary'] ?? '';

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
        if (softDelete($db, 'lessons', $id)) {
            $message = 'Lesson moved to trash.';
            $messageType = 'success';
        }
    } elseif ($action === 'restore') {
        $id = $_POST['id'] ?? '';
        if (restoreRecord($db, 'lessons', $id)) {
            $message = 'Lesson restored.';
            $messageType = 'success';
        }
    } elseif ($action === 'hard_delete') {
        $id = $_POST['id'] ?? '';
        if (hardDelete($db, 'lessons', $id)) {
            $message = 'Lesson permanently deleted.';
            $messageType = 'success';
        }
    }
}

$lessons = [];
try {
    if ($showTrash) {
        $lessons = $db->query("SELECT * FROM lessons WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $lessons = $db->query("SELECT * FROM lessons WHERE deleted_at IS NULL ORDER BY lesson_order ASC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { $lessons = []; }

$trashCount = 0;
try { $trashCount = $db->query("SELECT COUNT(*) FROM lessons WHERE deleted_at IS NOT NULL")->fetchColumn(); } catch (Exception $e) {}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lessons - Runyakitara Hub Admin</title>
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
                    <h2><i class="bi bi-book"></i> <?php echo $showTrash ? 'Trash' : 'All Lessons'; ?> (<?php echo count($lessons); ?>)</h2>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Search lessons..." onkeyup="searchTable()">
                            <i class="bi bi-search"></i>
                        </div>
                        <?php if ($showTrash): ?>
                            <a href="lessons-manage.php" class="btn-add" style="background:var(--text-light);"><i class="bi bi-arrow-left"></i> Back</a>
                        <?php else: ?>
                            <?php if ($trashCount > 0): ?>
                                <a href="?trash=1" class="btn-add" style="background:rgba(239,68,68,0.1);color:var(--danger);box-shadow:none;">
                                    <i class="bi bi-trash"></i> Trash (<?php echo $trashCount; ?>)
                                </a>
                            <?php endif; ?>
                            <button class="btn-add" onclick="openAddModal()">
                                <i class="bi bi-plus-circle"></i> Add Lesson
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <table id="lessonsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Level</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lessons)): ?>
                            <tr><td colspan="5" class="empty-state"><i class="bi bi-inbox"></i><p><?php echo $showTrash ? 'Trash is empty.' : 'No lessons yet. Add your first one!'; ?></p></td></tr>
                        <?php else: ?>
                            <?php foreach ($lessons as $lesson): ?>
                                <tr>
                                    <td><?php echo $lesson['lesson_order']; ?></td>
                                    <td>
                                        <strong 
                                            style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;" 
                                            title="<?php echo htmlspecialchars($lesson['title']); ?>">
                                            <?php echo htmlspecialchars($lesson['title']); ?>
                                        </strong>
                                    </td>
                                    <td><span class="level-badge level-<?php echo $lesson['level']; ?>"><?php echo ucfirst($lesson['level']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($lesson['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($showTrash): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="restore">
                                                    <input type="hidden" name="id" value="<?php echo $lesson['id']; ?>">
                                                    <button type="submit" class="btn-icon btn-view" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                </form>
                                                <button class="btn-delete-modal" onclick="openHardDeleteModal(<?php echo $lesson['id']; ?>, '<?php echo htmlspecialchars($lesson['title'], ENT_QUOTES); ?>')" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                                            <?php else: ?>
                                                <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode($lesson); ?>)' title="Edit"><i class="bi bi-pencil"></i></button>
                                                <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $lesson['id']; ?>, '<?php echo htmlspecialchars($lesson['title'], ENT_QUOTES); ?>')" title="Move to Trash"><i class="bi bi-trash"></i></button>
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

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="lessonModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="bi bi-book"></i> <span id="modalTitle">Add Lesson</span></h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" id="lessonForm" data-validate="true">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="lessonId">

                <div class="form-group">
                    <label class="required">Lesson Title</label>
                    <input type="text" name="title" id="modalLessonTitle" required minlength="5" maxlength="200" placeholder="e.g., Greetings and Introductions">
                </div>

                <div class="form-group">
                    <label class="required">Level</label>
                    <select name="level" id="modalLevel" required>
                        <option value="">Select Level</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required">Lesson Content</label>
                    <textarea name="content" id="modalContent" required minlength="50" placeholder="Enter the lesson content..."></textarea>
                </div>

                <div class="form-group">
                    <label>Vocabulary (Optional)</label>
                    <textarea name="vocabulary" id="modalVocabulary" placeholder="Enter vocabulary words and meanings..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()"><i class="bi bi-x-circle"></i> Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary"><i class="bi bi-check-circle"></i> <span id="submitBtnText">Add Lesson</span></button>
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
            <p id="deleteMessage">This lesson will be moved to trash and can be restored later.</p>
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
    document.getElementById('modalTitle').textContent = 'Add Lesson';
    document.getElementById('submitBtnText').textContent = 'Add Lesson';
    document.getElementById('formAction').value = 'add';
    document.getElementById('lessonForm').reset();
    document.getElementById('lessonId').value = '';
    resetFormValidation('lessonForm');
    document.getElementById('lessonModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function openEditModal(lesson) {
    document.getElementById('modalTitle').textContent = 'Edit Lesson';
    document.getElementById('submitBtnText').textContent = 'Update Lesson';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('lessonId').value = lesson.id;
    document.getElementById('modalLessonTitle').value = lesson.title;
    document.getElementById('modalLevel').value = lesson.level;
    document.getElementById('modalContent').value = lesson.content;
    document.getElementById('modalVocabulary').value = lesson.vocabulary || '';
    resetFormValidation('lessonForm');
    document.getElementById('lessonModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeModal() {
    document.getElementById('lessonModal').classList.remove('active');
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

document.getElementById('lessonModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });
document.getElementById('deleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeDeleteModal(); });
document.getElementById('hardDeleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeHardDeleteModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeDeleteModal(); closeHardDeleteModal(); } });
document.getElementById('mobileToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('active'));

function searchTable() {
    const filter = document.getElementById('searchInput').value.toUpperCase();
    document.querySelectorAll('#lessonsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toUpperCase().includes(filter) ? '' : 'none';
    });
}
</script>
<script src="js/form-validation.js"></script>
<script src="js/table-utils.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    TableUtils.init({ tableId: 'lessonsTable', rowsPerPage: 10, exportName: 'Lessons' });
});
</script>
</body>
</html>
