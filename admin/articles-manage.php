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

// Try to load RBAC if tables exist
$rbacEnabled = false;
try {
    require_once '../config/RBAC.php';
    $rbac = getRBAC($db);
    // Test if RBAC tables exist
    $rbac->getUserRoles();
    $rbacEnabled = true;
} catch (Exception $e) {
    // RBAC not set up yet, continue without it
    $rbacEnabled = false;
}

// Only check permissions if RBAC is enabled
if ($rbacEnabled) {
    requirePermission($db, 'articles.read');
}

$message = '';
$messageType = '';
$showTrash = isset($_GET['trash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Check create permission if RBAC is enabled
        if ($rbacEnabled && !$rbac->hasPermission('articles.create')) {
            $message = 'You do not have permission to create articles';
            $messageType = 'error';
        } else {
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $author = $_POST['author'] ?? '';
            $category = $_POST['category'] ?? '';
            
            $stmt = $db->prepare("INSERT INTO articles (title, content, author, category, created_at) VALUES (?, ?, ?, ?, datetime('now'))");
            if ($stmt->execute([$title, $content, $author, $category])) {
                $message = 'Article added successfully!';
                $messageType = 'success';
            }
        }
    } elseif ($action === 'edit') {
        // Check update permission if RBAC is enabled
        if ($rbacEnabled && !$rbac->hasPermission('articles.update')) {
            $message = 'You do not have permission to update articles';
            $messageType = 'error';
        } else {
            $id = $_POST['id'] ?? '';
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $author = $_POST['author'] ?? '';
            $category = $_POST['category'] ?? '';
            
            $stmt = $db->prepare("UPDATE articles SET title=?, content=?, author=?, category=? WHERE id=?");
            if ($stmt->execute([$title, $content, $author, $category, $id])) {
                $message = 'Article updated successfully!';
                $messageType = 'success';
            }
        }
    } elseif ($action === 'delete') {
        if ($rbacEnabled && !$rbac->hasPermission('articles.delete')) {
            $message = 'You do not have permission to delete articles';
            $messageType = 'error';
        } else {
            $id = $_POST['id'] ?? '';
            softDelete($db, 'articles', $id);
            $message = 'Article moved to trash.';
            $messageType = 'success';
        }
    } elseif ($action === 'restore') {
        $id = $_POST['id'] ?? '';
        restoreRecord($db, 'articles', $id);
        $message = 'Article restored.';
        $messageType = 'success';
    } elseif ($action === 'hard_delete') {
        $id = $_POST['id'] ?? '';
        hardDelete($db, 'articles', $id);
        $message = 'Article permanently deleted.';
        $messageType = 'success';
    }
}

$articles = [];
try {
    if ($showTrash) {
        $articles = $db->query("SELECT * FROM articles WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $articles = $db->query("SELECT * FROM articles WHERE deleted_at IS NULL ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { $articles = []; }

$trashCount = 0;
try { $trashCount = $db->query("SELECT COUNT(*) FROM articles WHERE deleted_at IS NOT NULL")->fetchColumn(); } catch (Exception $e) {}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Articles - Runyakitara Hub Admin</title>
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
                        <i class="bi bi-check-circle"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="content-table">
                    <div class="table-header">
                        <h2><i class="bi bi-newspaper"></i> <?php echo $showTrash ? 'Trash' : 'All Articles'; ?> (<?php echo count($articles); ?>)</h2>
                        <div style="display:flex;gap:12px;align-items:center;">
                            <?php if ($showTrash): ?>
                                <a href="articles-manage.php" class="btn-add" style="background:var(--text-light);"><i class="bi bi-arrow-left"></i> Back</a>
                            <?php else: ?>
                                <?php if ($trashCount > 0): ?>
                                    <a href="?trash=1" class="btn-add" style="background:rgba(239,68,68,0.1);color:var(--danger);box-shadow:none;"><i class="bi bi-trash"></i> Trash (<?php echo $trashCount; ?>)</a>
                                <?php endif; ?>
                                <button class="btn-add" onclick="openAddModal()"><i class="bi bi-plus-circle"></i> Add Article</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <table id="articlesTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($articles)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-light);">
                                        <i class="bi bi-newspaper" style="font-size: 48px; display: block; margin-bottom: 12px; opacity: 0.5;"></i>
                                        No articles found. Add your first article above!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($articles as $article): ?>
                                    <tr>
                                        <td>
                                            <strong 
                                                style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;"
                                                title="<?php echo htmlspecialchars($article['title']); ?>">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($article['author']); ?></td>
                                        <td>
                                            <span class="category-badge category-<?php echo $article['category']; ?>">
                                                <?php echo ucfirst($article['category']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($showTrash): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="restore">
                                                        <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                                        <button type="submit" class="btn-icon btn-view" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                    </form>
                                                    <button class="btn-delete-modal" onclick="openHardDeleteModal(<?php echo $article['id']; ?>, '<?php echo htmlspecialchars($article['title'], ENT_QUOTES); ?>')" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                                                <?php else: ?>
                                                    <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode($article); ?>)' title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $article['id']; ?>, '<?php echo htmlspecialchars($article['title'], ENT_QUOTES); ?>')" title="Move to Trash">
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
    
    <!-- Add/Edit Article Modal -->
    <div class="modal-overlay" id="articleModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2><i class="bi bi-newspaper"></i> <span id="modalTitle">Add Article</span></h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" id="articleForm" data-validate="true">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="articleId">
                    
                    <div class="form-group">
                        <label for="modalTitle" class="required">Article Title</label>
                        <input type="text" id="modalTitleInput" name="title" required minlength="5" maxlength="200"
                               placeholder="e.g., The History of Runyakitara Languages">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="modalAuthor" class="required">Author</label>
                            <input type="text" id="modalAuthor" name="author" required minlength="2" maxlength="100"
                                   placeholder="e.g., John Doe">
                        </div>
                        
                        <div class="form-group">
                            <label for="modalCategory" class="required">Category</label>
                            <select id="modalCategory" name="category" required>
                                <option value="">Select Category</option>
                                <option value="news">News</option>
                                <option value="culture">Culture</option>
                                <option value="language">Language</option>
                                <option value="history">History</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="modalContent" class="required">Article Content</label>
                        <textarea id="modalContent" name="content" required minlength="50"
                                  placeholder="Write your article content here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="modal-btn modal-btn-primary">
                        <i class="bi bi-check-circle"></i> <span id="submitBtnText">Add Article</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-container modal-sm">
            <div class="modal-body modal-confirm">
                <div class="modal-confirm-icon warning"><i class="bi bi-trash"></i></div>
                <h3>Move to Trash?</h3>
                <p id="deleteMessage">This article will be moved to trash and can be restored later.</p>
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
            document.getElementById('modalTitle').textContent = 'Add Article';
            document.getElementById('submitBtnText').textContent = 'Add Article';
            document.getElementById('formAction').value = 'add';
            document.getElementById('articleForm').reset();
            document.getElementById('articleId').value = '';
            resetFormValidation('articleForm');
            document.getElementById('articleModal').classList.add('active');
            document.body.classList.add('modal-open');
        }
        
        function openEditModal(article) {
            document.getElementById('modalTitle').textContent = 'Edit Article';
            document.getElementById('submitBtnText').textContent = 'Update Article';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('articleId').value = article.id;
            document.getElementById('modalTitleInput').value = article.title;
            document.getElementById('modalAuthor').value = article.author;
            document.getElementById('modalCategory').value = article.category;
            document.getElementById('modalContent').value = article.content;
            resetFormValidation('articleForm');
            document.getElementById('articleModal').classList.add('active');
            document.body.classList.add('modal-open');
        }
        
        function closeModal() {
            document.getElementById('articleModal').classList.remove('active');
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
        
        // Close modal on overlay click
        document.getElementById('articleModal')?.addEventListener('click', function(e) {
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
    </script>
    <script src="js/form-validation.js"></script>
    <script src="js/dashboard-common.js"></script>
    <script src="js/table-utils.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        TableUtils.init({ tableId: 'articlesTable', rowsPerPage: 10, exportName: 'Articles' });
    });
    </script>
</body>
</html>
