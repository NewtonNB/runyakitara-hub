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

$uploadDir = '../uploads/media/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        $filePath = '';

        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['media_file'];
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            $targetPath = $uploadDir . $fileName;

            $fileType = $file['type'];
            if (strpos($fileType, 'image/') === 0) $type = 'image';
            elseif (strpos($fileType, 'video/') === 0) $type = 'video';
            elseif (strpos($fileType, 'audio/') === 0) $type = 'audio';

            $allowedTypes = [
                'audio' => ['audio/mpeg','audio/mp3','audio/wav','audio/ogg'],
                'video' => ['video/mp4','video/mpeg','video/quicktime','video/x-msvideo','video/webm'],
                'image' => ['image/jpeg','image/jpg','image/png','image/gif','image/webp']
            ];

            if (isset($allowedTypes[$type]) && in_array($file['type'], $allowedTypes[$type])) {
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $filePath = 'uploads/media/' . $fileName;
                } else {
                    $message = 'Error uploading file. Check folder permissions.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Invalid file type: ' . $file['type'];
                $messageType = 'error';
            }
        } else {
            $message = 'Please select a file to upload.';
            $messageType = 'error';
        }

        if ($filePath) {
            $stmt = $db->prepare("INSERT INTO media (title, type, category, file_path, description, created_at) VALUES (?, ?, ?, ?, ?, datetime('now'))");
            if ($stmt->execute([$title, $type, $category, $filePath, $description])) {
                $message = 'Media added successfully!';
                $messageType = 'success';
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';

        $stmt = $db->prepare("SELECT file_path FROM media WHERE id=?");
        $stmt->execute([$id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        $filePath = $current['file_path'];

        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['media_file'];
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            $allowedTypes = [
                'audio' => ['audio/mpeg','audio/mp3','audio/wav','audio/ogg'],
                'video' => ['video/mp4','video/mpeg','video/quicktime','video/x-msvideo','video/webm'],
                'image' => ['image/jpeg','image/jpg','image/png','image/gif','image/webp']
            ];
            if (isset($allowedTypes[$type]) && in_array($file['type'], $allowedTypes[$type])) {
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                    if ($filePath && file_exists('../' . $filePath)) unlink('../' . $filePath);
                    $filePath = 'uploads/media/' . $fileName;
                }
            }
        }

        $stmt = $db->prepare("UPDATE media SET title=?, type=?, category=?, file_path=?, description=? WHERE id=?");
        if ($stmt->execute([$title, $type, $category, $filePath, $description, $id])) {
            $message = 'Media updated successfully!';
            $messageType = 'success';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (softDelete($db, 'media', $id)) {
            $message = 'Media moved to trash.';
            $messageType = 'success';
        }
    } elseif ($action === 'restore') {
        $id = $_POST['id'] ?? '';
        if (restoreRecord($db, 'media', $id)) {
            $message = 'Media restored.';
            $messageType = 'success';
        }
    } elseif ($action === 'hard_delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("SELECT file_path FROM media WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (hardDelete($db, 'media', $id)) {
            if ($row && $row['file_path'] && file_exists('../' . $row['file_path'])) {
                unlink('../' . $row['file_path']);
            }
            $message = 'Media permanently deleted.';
            $messageType = 'success';
        }
    }
}

$media = [];
try {
    if ($showTrash) {
        $media = $db->query("SELECT * FROM media WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $media = $db->query("SELECT * FROM media WHERE deleted_at IS NULL ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { $media = []; }

$trashCount = 0;
try { $trashCount = $db->query("SELECT COUNT(*) FROM media WHERE deleted_at IS NOT NULL")->fetchColumn(); } catch (Exception $e) {}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Media - Runyakitara Hub Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
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
                    <h2><i class="bi bi-play-circle"></i> <?php echo $showTrash ? 'Trash' : 'Media Library'; ?> (<?php echo count($media); ?>)</h2>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <?php if ($showTrash): ?>
                            <a href="media-manage.php" class="btn-add" style="background:var(--text-light);"><i class="bi bi-arrow-left"></i> Back</a>
                        <?php else: ?>
                            <?php if ($trashCount > 0): ?>
                                <a href="?trash=1" class="btn-add" style="background:rgba(239,68,68,0.1);color:var(--danger);box-shadow:none;">
                                    <i class="bi bi-trash"></i> Trash (<?php echo $trashCount; ?>)
                                </a>
                            <?php endif; ?>
                            <button class="btn-add" onclick="openAddModal()">
                                <i class="bi bi-plus-circle"></i> Add Media
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($media)): ?>
                    <div class="empty-state">
                        <i class="bi bi-<?php echo $showTrash ? 'trash' : 'play-circle'; ?>"></i>
                        <h3><?php echo $showTrash ? 'Trash is empty.' : 'No media yet'; ?></h3>
                        <?php if (!$showTrash): ?><p>Upload your first audio, video, or image!</p><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div id="mediaContainer" style="padding:24px;">
                        <div class="media-grid">
                            <?php foreach ($media as $item): ?>
                                <div class="media-card">
                                    <div class="media-thumbnail <?php echo $item['type']; ?>">
                                        <?php if ($item['type'] === 'image'): ?>
                                            <img src="../<?php echo htmlspecialchars($item['file_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                        <?php else: ?>
                                            <i class="bi bi-<?php echo $item['type'] === 'audio' ? 'music-note-beamed' : 'play-circle'; ?>"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="media-body">
                                        <div class="media-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <?php if ($item['description']): ?>
                                            <div class="media-description"><?php echo htmlspecialchars($item['description']); ?></div>
                                        <?php endif; ?>
                                        <div class="media-footer">
                                            <span class="type-badge type-<?php echo $item['type']; ?>"><?php echo ucfirst($item['type']); ?></span>
                                            <div class="action-buttons">
                                                <?php if ($showTrash): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="restore">
                                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn-icon btn-view" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                    </form>
                                                    <button class="btn-delete-modal" onclick="openHardDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?>')" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                                                <?php else: ?>
                                                    <a href="../<?php echo htmlspecialchars($item['file_path']); ?>" class="btn-icon" style="background:rgba(16,185,129,0.1);color:var(--success);" title="View/Play" target="_blank"><i class="bi bi-play-circle"></i></a>
                                                    <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode($item); ?>)' title="Edit"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?>')" title="Move to Trash"><i class="bi bi-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
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
<div class="modal-overlay" id="mediaModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="bi bi-play-circle"></i> <span id="modalTitle">Add Media</span></h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="mediaForm" data-validate="true">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="mediaId">

                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Media Title</label>
                        <input type="text" name="title" id="modalMediaTitle" required minlength="3" maxlength="200" placeholder="e.g., Pronunciation Guide">
                    </div>
                    <div class="form-group">
                        <label class="required">Media Type</label>
                        <select name="type" id="modalMediaType" required>
                            <option value="">Select Type</option>
                            <option value="audio">Audio</option>
                            <option value="video">Video</option>
                            <option value="image">Image</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" id="modalCategory" maxlength="50" placeholder="e.g., Lessons, Songs, Stories">
                </div>

                <div class="form-group">
                    <label id="fileLabel" class="required">Upload File</label>
                    <input type="file" name="media_file" id="modalFile" accept="audio/*,video/*,image/*">
                    <span class="field-hint" id="fileHint">Supported: MP3, WAV, OGG, MP4, WEBM, AVI, MOV, JPG, PNG, GIF, WEBP</span>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="modalDescription" placeholder="Describe this media..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()"><i class="bi bi-x-circle"></i> Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary"><i class="bi bi-upload"></i> <span id="submitBtnText">Upload Media</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Delete (Move to Trash) Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-container modal-sm">
        <div class="modal-body modal-confirm">
            <div class="modal-confirm-icon warning"><i class="bi bi-trash"></i></div>
            <h3>Move to Trash?</h3>
            <p id="deleteMessage">This media will be moved to trash and can be restored later.</p>
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
            <p id="hardDeleteMessage">This cannot be undone. The file will also be removed.</p>
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
    document.getElementById('modalTitle').textContent = 'Add Media';
    document.getElementById('submitBtnText').textContent = 'Upload Media';
    document.getElementById('formAction').value = 'add';
    document.getElementById('mediaForm').reset();
    document.getElementById('mediaId').value = '';
    document.getElementById('modalFile').required = true;
    document.getElementById('fileLabel').classList.add('required');
    document.getElementById('fileHint').textContent = 'Supported: MP3, WAV, OGG, MP4, WEBM, AVI, MOV, JPG, PNG, GIF, WEBP';
    resetFormValidation('mediaForm');
    document.getElementById('mediaModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function openEditModal(item) {
    document.getElementById('modalTitle').textContent = 'Edit Media';
    document.getElementById('submitBtnText').textContent = 'Update Media';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('mediaId').value = item.id;
    document.getElementById('modalMediaTitle').value = item.title;
    document.getElementById('modalMediaType').value = item.type;
    document.getElementById('modalCategory').value = item.category || '';
    document.getElementById('modalDescription').value = item.description || '';
    document.getElementById('modalFile').required = false;
    document.getElementById('fileLabel').classList.remove('required');
    document.getElementById('fileHint').textContent = 'Leave empty to keep current file: ' + (item.file_path ? item.file_path.split('/').pop() : '');
    resetFormValidation('mediaForm');
    document.getElementById('mediaModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeModal() {
    document.getElementById('mediaModal').classList.remove('active');
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
    document.getElementById('hardDeleteMessage').innerHTML = `Permanently delete "<strong>${title}</strong>"? The file will also be removed.`;
    document.getElementById('hardDeleteModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeHardDeleteModal() {
    document.getElementById('hardDeleteModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

document.getElementById('mediaModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });
document.getElementById('deleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeDeleteModal(); });
document.getElementById('hardDeleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeHardDeleteModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeDeleteModal(); closeHardDeleteModal(); } });
document.getElementById('mobileToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('active'));
</script>
<script src="js/form-validation.js"></script>
<script src="js/table-utils.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    TableUtils.initCards({ containerId: 'mediaContainer', cardSelector: '.media-card', rowsPerPage: 9, exportName: 'Media' });
});
</script>
</body>
</html>
