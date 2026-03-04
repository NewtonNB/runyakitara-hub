<?php
session_start();
require_once '../config/database.php';
require_once '../config/RBAC.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = getDBConnection();
$rbac = getRBAC($db);

// Require super_admin role for role management
requireRole($db, 'super_admin');

// Handle role assignment/revocation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'assign_role') {
        $userId = $_POST['user_id'] ?? 0;
        $roleName = $_POST['role_name'] ?? '';
        $expiresAt = $_POST['expires_at'] ?? null;
        
        if ($rbac->assignRole($userId, $roleName, $_SESSION['user_id'], $expiresAt)) {
            $success = "Role assigned successfully";
        } else {
            $error = "Failed to assign role";
        }
    } elseif ($action === 'revoke_role') {
        $userId = $_POST['user_id'] ?? 0;
        $roleName = $_POST['role_name'] ?? '';
        
        if ($rbac->revokeRole($userId, $roleName, $_SESSION['user_id'])) {
            $success = "Role revoked successfully";
        } else {
            $error = "Failed to revoke role";
        }
    }
}

// Get all users with their roles
$stmt = $db->query("
    SELECT u.id, u.username, u.email, u.created_at,
           GROUP_CONCAT(r.display_name, ', ') as roles
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_active = 1
    LEFT JOIN roles r ON ur.role_id = r.id
    GROUP BY u.id
    ORDER BY u.username
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all available roles
$allRoles = $rbac->getAllRoles();

include 'includes/header.php';
?>

<style>
    .roles-container {
        padding: 30px;
    }
    
    .page-header {
        margin-bottom: 30px;
    }
    
    .page-header h1 {
        font-size: 28px;
        color: #1f2937;
        margin-bottom: 10px;
    }
    
    .page-header p {
        color: #6b7280;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-success {
        background: #d1fae5;
        border: 1px solid #6ee7b7;
        color: #065f46;
    }
    
    .alert-error {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
    }
    
    .roles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .role-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 2px solid #e5e7eb;
    }
    
    .role-card h3 {
        font-size: 18px;
        color: #1f2937;
        margin-bottom: 8px;
    }
    
    .role-card .role-level {
        display: inline-block;
        padding: 4px 12px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .role-card p {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.5;
    }
    
    .users-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .table-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .table-header h2 {
        font-size: 20px;
        color: #1f2937;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead {
        background: #f9fafb;
    }
    
    th {
        padding: 15px 20px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    td {
        padding: 15px 20px;
        border-bottom: 1px solid #f3f4f6;
        color: #4b5563;
        font-size: 14px;
    }
    
    tbody tr:hover {
        background: #f9fafb;
    }
    
    .role-badge {
        display: inline-block;
        padding: 4px 10px;
        background: #e0e7ff;
        color: #3730a3;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .btn {
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-primary {
        background: #667eea;
        color: white;
    }
    
    .btn-primary:hover {
        background: #5568d3;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .modal.active {
        display: flex;
    }
    
    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        margin-bottom: 20px;
    }
    
    .modal-header h3 {
        font-size: 20px;
        color: #1f2937;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .form-group select,
    .form-group input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }
    
    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 25px;
    }
</style>

<div class="roles-container">
    <div class="page-header">
        <h1><i class="bi bi-shield-check"></i> Role & Permission Management</h1>
        <p>Manage user roles and access control</p>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <h2 style="margin-bottom: 20px; color: #1f2937;">Available Roles</h2>
    <div class="roles-grid">
        <?php foreach ($allRoles as $role): ?>
            <div class="role-card">
                <h3><?php echo htmlspecialchars($role['display_name']); ?></h3>
                <span class="role-level">Level <?php echo $role['level']; ?></span>
                <p><?php echo htmlspecialchars($role['description']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="users-table-container">
        <div class="table-header">
            <h2>User Role Assignments</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Current Roles</th>
                    <th>Member Since</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if ($user['roles']): ?>
                                <?php foreach (explode(', ', $user['roles']) as $role): ?>
                                    <span class="role-badge"><?php echo htmlspecialchars($role); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color: #9ca3af;">No roles assigned</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-primary" onclick="openAssignModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                <i class="bi bi-plus-circle"></i> Assign Role
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Assign Role Modal -->
<div id="assignModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Assign Role to <span id="modalUsername"></span></h3>
        </div>
        <form method="POST" data-validate="true">
            <input type="hidden" name="action" value="assign_role">
            <input type="hidden" name="user_id" id="modalUserId">
            
            <div class="form-group">
                <label>Select Role</label>
                <select name="role_name" required>
                    <option value="">Choose a role...</option>
                    <?php foreach ($allRoles as $role): ?>
                        <option value="<?php echo htmlspecialchars($role['name']); ?>">
                            <?php echo htmlspecialchars($role['display_name']); ?> (Level <?php echo $role['level']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Expiration Date (Optional)</label>
                <input type="datetime-local" name="expires_at">
                <small style="color: #6b7280; font-size: 12px;">Leave empty for permanent assignment</small>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn" onclick="closeModal()" style="background: #e5e7eb; color: #374151;">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign Role</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignModal(userId, username) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUsername').textContent = username;
    document.getElementById('assignModal').classList.add('active');
}

function closeModal() {
    document.getElementById('assignModal').classList.remove('active');
}

// Close modal on outside click
document.getElementById('assignModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php
closeDBConnection($db);
?>
<script src="js/form-validation.js"></script>
