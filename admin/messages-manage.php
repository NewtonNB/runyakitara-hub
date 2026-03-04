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
    
    if ($action === 'update_status') {
        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        $stmt = $db->prepare("UPDATE contact_messages SET status=? WHERE id=?");
        if ($stmt->execute([$status, $id])) {
            $message = 'Status updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating status.';
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id=?");
        if ($stmt->execute([$id])) {
            $message = 'Message deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting message.';
            $messageType = 'error';
        }
    }
}

// Get all messages
$messages = [];
try {
    $stmt = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $messages = [];
}

// Get message for viewing
$viewMessage = null;
if (isset($_GET['view'])) {
    $viewId = $_GET['view'];
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id=?");
    $stmt->execute([$viewId]);
    $viewMessage = $stmt->fetch(PDO::FETCH_ASSOC);
}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - Runyakitara Hub Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    
    <style>
        .messages-grid {
            display: grid;
            gap: 20px;
        }
        
        .message-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            cursor: pointer;
            border-left: 4px solid transparent;
        }
        
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .message-card.new {
            border-left-color: var(--info);
            background: linear-gradient(to right, rgba(59, 130, 246, 0.05), white);
        }
        
        .message-card.pending {
            border-left-color: var(--warning);
        }
        
        .message-card.completed {
            border-left-color: var(--success);
            opacity: 0.8;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .message-sender {
            flex: 1;
        }
        
        .sender-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }
        
        .sender-email {
            font-size: 14px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .message-status {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-new {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }
        
        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .status-completed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .message-subject {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 12px;
        }
        
        .message-preview {
            font-size: 14px;
            color: var(--text);
            line-height: 1.6;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .message-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }
        
        .message-date {
            font-size: 13px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .message-actions {
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
        
        .btn-view {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary);
        }
        
        .btn-view:hover {
            background: var(--primary);
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
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 700px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            padding: 30px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .modal-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: var(--text-light);
            cursor: pointer;
            transition: var(--transition);
            padding: 0;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        
        .modal-close:hover {
            background: var(--light);
            color: var(--text);
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .message-detail {
            margin-bottom: 24px;
        }
        
        .detail-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-light);
            margin-bottom: 8px;
        }
        
        .detail-value {
            font-size: 15px;
            color: var(--text);
            line-height: 1.6;
        }
        
        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .filter-tab {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            background: var(--light);
            color: var(--text);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
        }
        
        .filter-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .filter-tab:hover {
            transform: translateY(-2px);
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
                
                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterMessages('all')">
                        All Messages (<?php echo count($messages); ?>)
                    </button>
                    <button class="filter-tab" onclick="filterMessages('new')">
                        New (<?php echo count(array_filter($messages, fn($m) => $m['status'] === 'new')); ?>)
                    </button>
                    <button class="filter-tab" onclick="filterMessages('pending')">
                        Pending (<?php echo count(array_filter($messages, fn($m) => $m['status'] === 'pending')); ?>)
                    </button>
                    <button class="filter-tab" onclick="filterMessages('completed')">
                        Completed (<?php echo count(array_filter($messages, fn($m) => $m['status'] === 'completed')); ?>)
                    </button>
                </div>
                
                <!-- Messages Grid -->
                <div class="messages-grid">
                    <?php if (empty($messages)): ?>
                        <div style="background: white; border-radius: 16px; padding: 60px; text-align: center; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
                            <i class="bi bi-inbox" style="font-size: 64px; color: var(--text-light); opacity: 0.5; display: block; margin-bottom: 16px;"></i>
                            <h3 style="color: var(--text); margin-bottom: 8px;">No messages yet</h3>
                            <p style="color: var(--text-light);">Messages from your contact form will appear here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-card <?php echo $msg['status']; ?>" data-status="<?php echo $msg['status']; ?>">
                                <div class="message-header">
                                    <div class="message-sender">
                                        <div class="sender-name"><?php echo htmlspecialchars($msg['name']); ?></div>
                                        <div class="sender-email">
                                            <i class="bi bi-envelope"></i>
                                            <?php echo htmlspecialchars($msg['email']); ?>
                                        </div>
                                    </div>
                                    <span class="message-status status-<?php echo $msg['status']; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="message-subject">
                                    <?php echo htmlspecialchars($msg['subject']); ?>
                                </div>
                                
                                <div class="message-preview">
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                </div>
                                
                                <div class="message-footer">
                                    <div class="message-date">
                                        <i class="bi bi-clock"></i>
                                        <?php echo date('M d, Y \a\t g:i A', strtotime($msg['created_at'])); ?>
                                    </div>
                                    <div class="message-actions">
                                        <button class="btn-icon btn-view" onclick="viewMessage(<?php echo $msg['id']; ?>)" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                            <button type="submit" class="btn-icon btn-delete" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Message Modal -->
    <div class="modal" id="messageModal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 id="modalSubject">Message Details</h2>
                </div>
                <button class="modal-close" onclick="closeModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <form method="POST" style="display: flex; gap: 12px; width: 100%;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" id="modalMessageId">
                    <select name="status" class="btn btn-secondary" style="flex: 1;">
                        <option value="new">New</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i>
                        Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('mobileToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        const messagesData = <?php echo json_encode($messages); ?>;
        
        function viewMessage(id) {
            const message = messagesData.find(m => m.id == id);
            if (!message) return;
            
            document.getElementById('modalSubject').textContent = message.subject;
            document.getElementById('modalMessageId').value = message.id;
            document.querySelector('select[name="status"]').value = message.status;
            
            document.getElementById('modalBody').innerHTML = `
                <div class="message-detail">
                    <div class="detail-label">From</div>
                    <div class="detail-value"><strong>${message.name}</strong></div>
                </div>
                <div class="message-detail">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">${message.email}</div>
                </div>
                <div class="message-detail">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">${new Date(message.created_at).toLocaleString()}</div>
                </div>
                <div class="message-detail">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="message-status status-${message.status}">${message.status.charAt(0).toUpperCase() + message.status.slice(1)}</span>
                    </div>
                </div>
                <div class="message-detail">
                    <div class="detail-label">Message</div>
                    <div class="detail-value" style="white-space: pre-wrap;">${message.message}</div>
                </div>
            `;
            
            document.getElementById('messageModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('messageModal').classList.remove('active');
        }
        
        function filterMessages(status) {
            const cards = document.querySelectorAll('.message-card');
            const tabs = document.querySelectorAll('.filter-tab');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Close modal on outside click
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
