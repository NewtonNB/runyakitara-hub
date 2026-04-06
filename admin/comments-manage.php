<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($id) {
        if ($action === 'approve') {
            $stmt = $db->prepare("UPDATE comments SET status='approved' WHERE id=?");
            $stmt->execute([$id]);
        } elseif ($action === 'reject') {
            $stmt = $db->prepare("UPDATE comments SET status='rejected' WHERE id=?");
            $stmt->execute([$id]);
        } elseif ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM comments WHERE id=?");
            $stmt->execute([$id]);
        }
    }

    closeDBConnection($db);
    header('Location: comments-manage.php?updated=1');
    exit;
}

// Flash message
$message = '';
$messageType = '';
if (isset($_GET['updated'])) { $message = 'Comment updated successfully!'; $messageType = 'success'; }

// Fetch all comments
$comments = [];
try {
    $comments = $db->query("SELECT * FROM comments ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $comments = [];
}

// Count per status for filter tabs
$countAll      = count($comments);
$countPending  = count(array_filter($comments, fn($c) => $c['status'] === 'pending'));
$countApproved = count(array_filter($comments, fn($c) => $c['status'] === 'approved'));
$countRejected = count(array_filter($comments, fn($c) => $c['status'] === 'rejected'));

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments - Runyakitara Hub Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin-responsive.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/modals.css">
    <link rel="stylesheet" href="css/table-utils.css">
    <style>
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: white;
            padding: 16px 20px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
            border: 1px solid rgba(226,232,240,0.8);
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 9px 18px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: var(--light);
            color: var(--text);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
            font-family: inherit;
        }
        .filter-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
        }
        .filter-tab:hover:not(.active) { border-color: var(--primary); color: var(--primary); }

        /* Status badges */
        .badge-approved { background: rgba(16,185,129,0.12); color: #059669; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .badge-pending  { background: rgba(245,158,11,0.12);  color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .badge-rejected { background: rgba(239,68,68,0.12);   color: #dc2626; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; }

        /* Approve button */
        .btn-approve {
            background: rgba(16,185,129,0.1);
            color: #059669;
        }
        .btn-approve:hover { background: #10b981; color: white; transform: scale(1.1); }

        /* Reject button */
        .btn-reject {
            background: rgba(245,158,11,0.1);
            color: #d97706;
        }
        .btn-reject:hover { background: #f59e0b; color: white; transform: scale(1.1); }

        #commentsTable { table-layout: fixed; }
        #commentsTable th:nth-child(1), #commentsTable td:nth-child(1) { width: 44px; text-align: center; }
        #commentsTable th:nth-child(2), #commentsTable td:nth-child(2) { width: 110px; }
        #commentsTable th:nth-child(3), #commentsTable td:nth-child(3) { width: 80px; text-align: center; }
        #commentsTable th:nth-child(4), #commentsTable td:nth-child(4) { width: 130px; }
        #commentsTable th:nth-child(5), #commentsTable td:nth-child(5) { width: auto; }
        #commentsTable th:nth-child(6), #commentsTable td:nth-child(6) { width: 110px; text-align: center; }
        #commentsTable th:nth-child(7), #commentsTable td:nth-child(7) { width: 110px; white-space: nowrap; }
        #commentsTable th:nth-child(8), #commentsTable td:nth-child(8) { width: 130px; text-align: center; }
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
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="filterComments('all', this)">All (<?php echo $countAll; ?>)</button>
                <button class="filter-tab" onclick="filterComments('pending', this)">Pending (<?php echo $countPending; ?>)</button>
                <button class="filter-tab" onclick="filterComments('approved', this)">Approved (<?php echo $countApproved; ?>)</button>
                <button class="filter-tab" onclick="filterComments('rejected', this)">Rejected (<?php echo $countRejected; ?>)</button>
            </div>

            <!-- Comments Table -->
            <div class="content-table">
                <div class="table-header">
                    <h2><i class="bi bi-chat-dots"></i> Comments (<?php echo $countAll; ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table id="commentsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Content Type</th>
                                <th>Content ID</th>
                                <th>Name</th>
                                <th>Comment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($comments)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;padding:50px;color:var(--text-light);">
                                        <i class="bi bi-chat-dots" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.4;"></i>
                                        No comments yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($comments as $i => $c): ?>
                                <tr data-status="<?php echo htmlspecialchars($c['status']); ?>">
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($c['content_type'] ?? '—')); ?></td>
                                    <td style="text-align:center;"><?php echo (int)($c['content_id'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars($c['name'] ?? $c['author_name'] ?? '—'); ?></td>
                                    <td title="<?php echo htmlspecialchars($c['content'] ?? $c['comment'] ?? ''); ?>">
                                        <?php
                                            $text = $c['content'] ?? $c['comment'] ?? '';
                                            echo htmlspecialchars(mb_strlen($text) > 80 ? mb_substr($text, 0, 80) . '…' : $text);
                                        ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge-<?php echo htmlspecialchars($c['status']); ?>">
                                            <?php echo ucfirst($c['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons" style="justify-content:center;">
                                            <?php if ($c['status'] !== 'approved'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" class="btn-icon btn-approve" title="Approve">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <?php if ($c['status'] !== 'rejected'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" class="btn-icon btn-reject" title="Reject">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <button class="btn-icon btn-delete" title="Delete"
                                                onclick="openDeleteModal(<?php echo $c['id']; ?>, <?php echo htmlspecialchars(json_encode(mb_substr($c['content'] ?? $c['comment'] ?? '', 0, 60)), ENT_QUOTES); ?>)">
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
            </div>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-container modal-sm">
        <div class="modal-body modal-confirm">
            <div class="modal-confirm-icon danger">
                <i class="bi bi-trash"></i>
            </div>
            <h3>Delete Comment?</h3>
            <p id="deleteMessage">This comment will be permanently deleted and cannot be recovered.</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <div style="display:flex;gap:12px;justify-content:center;">
                    <button type="button" class="modal-btn modal-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="modal-btn modal-btn-danger"><i class="bi bi-trash"></i> Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('mobileToggle')?.addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
    });

    function filterComments(status, btn) {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('#commentsTable tbody tr[data-status]').forEach(row => {
            row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
        });
    }

    function openDeleteModal(id, preview) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteMessage').textContent =
            'Delete comment: "' + (preview || '...') + '"? This cannot be undone.';
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }

    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>
</body>
</html>
