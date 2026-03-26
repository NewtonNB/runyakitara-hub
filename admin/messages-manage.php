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
    <link rel="stylesheet" href="css/admin-responsive.css">
    <link rel="stylesheet" href="css/forms.css">
    <style>
        .messages-grid { display: grid; gap: 16px; }
        .message-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
            border: 1px solid rgba(226,232,240,0.8);
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .message-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .message-card.new     { border-left-color: var(--info); background: linear-gradient(to right, rgba(59,130,246,0.04), white); }
        .message-card.pending { border-left-color: var(--warning); }
        .message-card.completed { border-left-color: var(--success); opacity: 0.85; }
        .message-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
        .message-sender { flex: 1; }
        .sender-name { font-size: 17px; font-weight: 700; color: var(--dark); margin-bottom: 4px; }
        .sender-email { font-size: 13px; color: var(--text-light); display: flex; align-items: center; gap: 6px; }
        .message-status { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .status-new       { background: rgba(59,130,246,0.1); color: var(--info); }
        .status-pending   { background: rgba(245,158,11,0.1); color: #d97706; }
        .status-completed { background: rgba(16,185,129,0.1); color: var(--success); }
        .message-subject { font-size: 15px; font-weight: 600; color: var(--dark); margin-bottom: 10px; }
        .message-preview { font-size: 14px; color: var(--text); line-height: 1.6; margin-bottom: 14px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .message-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--border); }
        .message-date { font-size: 13px; color: var(--text-light); display: flex; align-items: center; gap: 6px; }
        .message-actions { display: flex; gap: 8px; }
        .filter-tabs { display: flex; gap: 10px; margin-bottom: 20px; background: white; padding: 16px 20px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04); border: 1px solid rgba(226,232,240,0.8); flex-wrap: wrap; }
        .filter-tab { padding: 9px 18px; border-radius: 10px; border: 1.5px solid var(--border); background: var(--light); color: var(--text); font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 13px; font-family: inherit; }
        .filter-tab.active { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-color: transparent; }
        .filter-tab:hover:not(.active) { border-color: var(--primary); color: var(--primary); }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 20px; max-width: 680px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
        .modal-header { padding: 28px 30px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: flex-start; }
        .modal-header h2 { font-size: 22px; font-weight: 700; color: var(--dark); margin: 0; }
        .modal-close { background: var(--light); border: none; font-size: 20px; color: var(--text-light); cursor: pointer; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
        .modal-close:hover { background: var(--border); color: var(--text); }
        .modal-body { padding: 28px 30px; }
        .message-detail { margin-bottom: 20px; }
        .detail-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-light); margin-bottom: 6px; }
        .detail-value { font-size: 15px; color: var(--text); line-height: 1.6; }
        .modal-footer { padding: 20px 30px; border-top: 1px solid var(--border); display: flex; gap: 12px; }
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
