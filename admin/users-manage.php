<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, datetime('now'))");
        if ($stmt->execute([$username, $email, $hashedPassword, $role])) {
            $message = 'User added successfully!';
            $messageType = 'success';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        $password = $_POST['password'] ?? '';
        
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET username=?, email=?, password=?, role=? WHERE id=?");
            $stmt->execute([$username, $email, $hashedPassword, $role, $id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
            $stmt->execute([$username, $email, $role, $id]);
        }
        $message = 'User updated successfully!';
        $messageType = 'success';
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id != $_SESSION['user_id']) {
            $stmt = $db->prepare("DELETE FROM users WHERE id=?");
            if ($stmt->execute([$id])) {
                $message = 'User deleted successfully!';
                $messageType = 'success';
            }
        } else {
            $message = 'Cannot delete your own account!';
            $messageType = 'error';
        }
    }
}

$users = [];
try {
    $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}

$editUser = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Runyakitara Hub Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/form-validation.css">
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
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }
        .user-details {
            flex: 1;
        }
        .user-name {
            font-weight: 600;
            color: var(--dark);
        }
        .user-email {
            font-size: 13px;
            color: var(--text-light);
        }
        .role-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .role-admin {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary);
        }
        .role-editor {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
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
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .form-hint {
            font-size: 13px;
            color: var(--text-light);
            margin-top: 4px;
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
                
                <div class="form-section">
                    <h2>
                        <i class="bi bi-<?php echo $editUser ? 'pencil' : 'plus-circle'; ?>"></i>
                        <?php echo $editUser ? 'Edit User' : 'Add New User'; ?>
                    </h2>
                    <form method="POST" data-validate="true">
                        <input type="hidden" name="action" value="<?php echo $editUser ? 'edit' : 'add'; ?>">
                        <?php if ($editUser): ?>
                            <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input type="text" id="username" name="username" required 
                                       value="<?php echo $editUser ? htmlspecialchars($editUser['username']) : ''; ?>"
                                       placeholder="e.g., johndoe">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo $editUser ? htmlspecialchars($editUser['email']) : ''; ?>"
                                       placeholder="e.g., john@example.com">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password <?php echo $editUser ? '' : '*'; ?></label>
                                <input type="password" id="password" name="password" <?php echo $editUser ? '' : 'required'; ?>
                                       placeholder="<?php echo $editUser ? 'Leave blank to keep current' : 'Enter password'; ?>">
                                <?php if ($editUser): ?>
                                    <div class="form-hint">Leave blank to keep current password</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role *</label>
                                <select id="role" name="role" required>
                                    <option value="admin" <?php echo ($editUser && $editUser['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="editor" <?php echo ($editUser && $editUser['role'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-<?php echo $editUser ? 'check' : 'plus'; ?>-circle"></i>
                                <?php echo $editUser ? 'Update User' : 'Add User'; ?>
                            </button>
                            <?php if ($editUser): ?>
                                <a href="users-manage.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <div class="content-table">
                    <div class="table-header">
                        <h2><i class="bi bi-people"></i> All Users (<?php echo count($users); ?>)</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-light);">
                                        <i class="bi bi-people" style="font-size: 48px; display: block; margin-bottom: 12px; opacity: 0.5;"></i>
                                        No users found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?edit=<?php echo $user['id']; ?>" class="btn-icon btn-edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn-icon btn-delete" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
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
    
    <script>
        document.getElementById('mobileToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
    <script src="js/form-validation.js"></script>
</body>
</html>
