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
    $users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $users = []; }

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
    <link rel="stylesheet" href="css/admin-responsive.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/modals.css">
    <style>
        .user-info { display:flex; align-items:center; gap:12px; }
        .user-avatar-sm { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--secondary)); display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:16px; flex-shrink:0; }
        .user-name { font-weight:600; color:var(--dark); }
        .user-email { font-size:13px; color:var(--text-light); }
        .role-badge { padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; display:inline-block; }
        .role-admin  { background:rgba(102,126,234,0.1); color:var(--primary); }
        .role-editor { background:rgba(16,185,129,0.1); color:var(--success); }
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
                    <h2><i class="bi bi-people"></i> All Users (<?php echo count($users); ?>)</h2>
                    <button class="btn-add" onclick="openAddModal()">
                        <i class="bi bi-plus-circle"></i> Add User
                    </button>
                </div>
                <table id="usersTable">                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="4" class="empty-state"><i class="bi bi-people"></i><p>No users found.</p></td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar-sm"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                                            <div>
                                                <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit-modal" onclick='openEditModal(<?php echo json_encode(['id'=>$user['id'],'username'=>$user['username'],'email'=>$user['email'],'role'=>$user['role']]); ?>)' title="Edit"><i class="bi bi-pencil"></i></button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn-delete-modal" onclick="openDeleteModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')" title="Delete"><i class="bi bi-trash"></i></button>
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
<div class="modal-overlay" id="userModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="bi bi-person-circle"></i> <span id="modalTitle">Add User</span></h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" id="userForm">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="userId">

                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Username</label>
                        <input type="text" name="username" id="modalUsername" required placeholder="e.g., johndoe">
                    </div>
                    <div class="form-group">
                        <label class="required">Email</label>
                        <input type="email" name="email" id="modalEmail" required placeholder="e.g., john@example.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label id="passwordLabel" class="required">Password</label>
                        <input type="password" name="password" id="modalPassword" placeholder="Enter password" autocomplete="new-password">
                        <span class="field-hint" id="passwordHint"></span>
                    </div>
                    <div class="form-group">
                        <label class="required">Role</label>
                        <select name="role" id="modalRole" required>
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal()"><i class="bi bi-x-circle"></i> Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary"><i class="bi bi-check-circle"></i> <span id="submitBtnText">Add User</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-container modal-sm">
        <div class="modal-body modal-confirm">
            <div class="modal-confirm-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
            <h3>Delete User?</h3>
            <p id="deleteMessage">Are you sure?</p>
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
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add User';
    document.getElementById('submitBtnText').textContent = 'Add User';
    document.getElementById('formAction').value = 'add';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('modalPassword').required = true;
    document.getElementById('passwordLabel').classList.add('required');
    document.getElementById('passwordHint').textContent = '';
    document.getElementById('userModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function openEditModal(user) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('submitBtnText').textContent = 'Update User';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('userId').value = user.id;
    document.getElementById('modalUsername').value = user.username;
    document.getElementById('modalEmail').value = user.email;
    document.getElementById('modalRole').value = user.role;
    document.getElementById('modalPassword').value = '';
    document.getElementById('modalPassword').required = false;
    document.getElementById('passwordLabel').classList.remove('required');
    document.getElementById('passwordHint').textContent = 'Leave blank to keep current password';
    document.getElementById('userModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeModal() {
    document.getElementById('userModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

function openDeleteModal(id, username) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteMessage').innerHTML = `Delete user "<strong>${username}</strong>"?`;
    document.getElementById('deleteModal').classList.add('active');
    document.body.classList.add('modal-open');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.classList.remove('modal-open');
}

document.getElementById('userModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });
document.getElementById('deleteModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeDeleteModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeDeleteModal(); } });
document.getElementById('mobileToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('active'));
</script>
</body>
</html>
