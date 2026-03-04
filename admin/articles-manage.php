<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$db = getDBConnection();

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
        // Check delete permission if RBAC is enabled
        if ($rbacEnabled && !$rbac->hasPermission('articles.delete')) {
            $message = 'You do not have permission to delete articles';
            $messageType = 'error';
        } else {
            $id = $_POST['id'] ?? '';
            $stmt = $db->prepare("DELETE FROM articles WHERE id=?");
            if ($stmt->execute([$id])) {
                $message = 'Article deleted successfully!';
                $messageType = 'success';
            }
        }
    }
}

$articles = [];
try {
    $stmt = $db->query("SELECT * FROM articles ORDER BY created_at DESC");
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $articles = [];
}

$editArticle = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM articles WHERE id=?");
    $stmt->execute([$editId]);
    $editArticle = $stmt->fetch(PDO::FETCH_ASSOC);
}

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
        }
        .category-news {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }
        .category-culture {
            background: rgba(139, 69, 19, 0.1);
            color: #8b4513;
        }
        .category-language {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary);
        }
        .category-history {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
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
            min-height: 200px;
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
                
                <div class="content-table">
                    <div class="table-header">
                        <h2><i class="bi bi-newspaper"></i> All Articles (<?php echo count($articles); ?>)</h2>
                        <button class="btn-add" onclick="openAddModal()">
                            <i class="bi bi-plus-circle"></i>
                            Add Article
                        </button>
                    </div>
                    <table>
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
                                        <td><strong><?php echo htmlspecialchars($article['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($article['author']); ?></td>
                                        <td>
                                            <span class="category-badge category-<?php echo $article['category']; ?>">
                                                <?php echo ucfirst($article['category']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode($article); ?>)' title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $article['id']; ?>, '<?php echo htmlspecialchars($article['title'], ENT_QUOTES); ?>')" title="Delete">
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
                <div class="modal-confirm-icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h3>Delete Article?</h3>
                <p id="deleteMessage">Are you sure you want to delete this article? This action cannot be undone.</p>
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
            document.getElementById('modalTitle').textContent = 'Add Article';
            document.getElementById('submitBtnText').textContent = 'Add Article';
            document.getElementById('formAction').value = 'add';
            document.getElementById('articleForm').reset();
            document.getElementById('articleId').value = '';
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
            document.getElementById('articleModal').classList.add('active');
            document.body.classList.add('modal-open');
        }
        
        function closeModal() {
            document.getElementById('articleModal').classList.remove('active');
            document.body.classList.remove('modal-open');
        }
        
        function openDeleteModal(id, title) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete "<strong>${title}</strong>"? This action cannot be undone.`;
            document.getElementById('deleteModal').classList.add('active');
            document.body.classList.add('modal-open');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            document.body.classList.remove('modal-open');
        }
        
        // Close modal on overlay click
        document.getElementById('articleModal')?.addEventListener('click', function(e) {
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
    </script>
    <script src="js/form-validation.js"></script>
    <script src="js/dashboard-common.js"></script>
</body>
</html>
