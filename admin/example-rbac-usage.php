<?php
/**
 * EXAMPLE: Admin Page with RBAC Integration
 * This file demonstrates best practices for implementing RBAC
 * Copy this pattern to your other admin pages
 */

session_start();

// 1. Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Include required files
require_once '../config/database.php';
require_once '../config/RBAC.php';
require_once 'includes/rbac-helpers.php';

$db = getDBConnection();
$rbac = getRBAC($db);

// 3. Require minimum permission to access this page
requirePermission($db, 'articles.read');

$message = '';
$messageType = '';

// 4. Handle form submissions with permission checks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        // Check create permission
        if (!$rbac->hasPermission('articles.create')) {
            $message = 'You do not have permission to create articles';
            $messageType = 'error';
        } else {
            // Process creation
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            
            $stmt = $db->prepare("INSERT INTO articles (title, content, created_at) VALUES (?, ?, datetime('now'))");
            if ($stmt->execute([$title, $content])) {
                $message = 'Article created successfully!';
                $messageType = 'success';
            }
        }
    } elseif ($action === 'update') {
        // Check update permission
        if (!$rbac->hasPermission('articles.update')) {
            $message = 'You do not have permission to update articles';
            $messageType = 'error';
        } else {
            // Process update
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            
            $stmt = $db->prepare("UPDATE articles SET title = ?, content = ? WHERE id = ?");
            if ($stmt->execute([$title, $content, $id])) {
                $message = 'Article updated successfully!';
                $messageType = 'success';
            }
        }
    } elseif ($action === 'delete') {
        // Check delete permission
        if (!$rbac->hasPermission('articles.delete')) {
            $message = 'You do not have permission to delete articles';
            $messageType = 'error';
        } else {
            // Process deletion
            $id = $_POST['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
            if ($stmt->execute([$id])) {
                $message = 'Article deleted successfully!';
                $messageType = 'success';
            }
        }
    }
}

// 5. Fetch data
$stmt = $db->query("SELECT * FROM articles ORDER BY created_at DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<style>
    .page-container {
        padding: 30px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 500;
    }
    
    .btn-primary {
        background: #667eea;
        color: white;
    }
    
    .table-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-icon {
        padding: 8px 12px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        background: #f3f4f6;
        color: #374151;
    }
    
    .btn-danger {
        background: #fee2e2;
        color: #991b1b;
    }
</style>

<div class="page-container">
    <!-- 6. Page header with conditional create button -->
    <div class="page-header">
        <div>
            <h1>Articles Management</h1>
            <p>Manage your articles and content</p>
        </div>
        
        <?php
        // Method 1: Using helper function
        renderActionButton('articles.create', 'Create Article', '?action=new', 'plus-circle');
        
        // Method 2: Manual check
        // if ($rbac->hasPermission('articles.create')): ?>
        <!--
            <a href="?action=new" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Article
            </a>
        -->
        <?php // endif; ?>
    </div>
    
    <!-- 7. Display messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- 8. Display permission info (for demo purposes) -->
    <div style="background: #f0f9ff; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 10px;">Your Permissions:</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php
            $permissions = ['articles.create', 'articles.read', 'articles.update', 'articles.delete'];
            foreach ($permissions as $perm) {
                echo getPermissionBadge($perm) . ' ';
            }
            ?>
        </div>
        
        <h3 style="margin: 15px 0 10px;">Your Roles:</h3>
        <div>
            <?php
            $roles = getUserRoleNames();
            foreach ($roles as $role) {
                echo "<span class='badge badge-primary'>{$role}</span> ";
            }
            ?>
        </div>
    </div>
    
    <!-- 9. Content table with conditional actions -->
    <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden;">
        <thead style="background: #f9fafb;">
            <tr>
                <th style="padding: 15px; text-align: left;">Title</th>
                <th style="padding: 15px; text-align: left;">Created</th>
                <th style="padding: 15px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 15px;"><?php echo htmlspecialchars($article['title']); ?></td>
                    <td style="padding: 15px;"><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                    <td style="padding: 15px;">
                        <?php
                        // Method 1: Using helper function
                        renderTableActions($article['id'], 'articles', '?action=edit', 'delete');
                        
                        // Method 2: Manual implementation
                        /*
                        echo '<div class="table-actions">';
                        
                        if ($rbac->can('articles', 'update')) {
                            echo "<a href='?action=edit&id={$article['id']}' class='btn-icon'>
                                    <i class='bi bi-pencil'></i>
                                  </a>";
                        }
                        
                        if ($rbac->can('articles', 'delete')) {
                            echo "<form method='POST' style='display:inline;'>
                                    <input type='hidden' name='action' value='delete'>
                                    <input type='hidden' name='id' value='{$article['id']}'>
                                    <button type='submit' class='btn-icon btn-danger'>
                                        <i class='bi bi-trash'></i>
                                    </button>
                                  </form>";
                        }
                        
                        echo '</div>';
                        */
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- 10. Conditional sections based on roles -->
    <?php ifRole('super_admin', function() { ?>
        <div style="margin-top: 30px; padding: 20px; background: #fef3c7; border-radius: 10px;">
            <h3>Super Admin Section</h3>
            <p>This content is only visible to super administrators.</p>
        </div>
    <?php }); ?>
    
    <!-- 11. Multiple permission checks -->
    <?php
    $perms = checkPermissions(['articles.create', 'articles.update', 'articles.delete']);
    if ($perms['articles.create'] && $perms['articles.update'] && $perms['articles.delete']):
    ?>
        <div style="margin-top: 20px; padding: 15px; background: #d1fae5; border-radius: 10px;">
            <i class="bi bi-check-circle"></i> You have full article management permissions
        </div>
    <?php endif; ?>
</div>

<?php
closeDBConnection($db);
?>

<!-- 
SUMMARY OF RBAC PATTERNS:

1. Page-level protection:
   requirePermission($db, 'resource.action');

2. Action-level checks:
   if ($rbac->hasPermission('resource.action')) { }

3. Helper functions:
   - renderActionButton()
   - renderTableActions()
   - ifCan() / ifRole()
   - canDo()

4. Manual checks:
   - $rbac->hasPermission('permission.name')
   - $rbac->hasRole('role_name')
   - $rbac->can('resource', 'action')

5. Multiple checks:
   - $rbac->hasAnyPermission([...])
   - $rbac->hasAllPermissions([...])
   - checkPermissions([...])

6. Role checks:
   - isSuperAdmin()
   - getUserRoleNames()
   - $rbac->hasAnyRole([...])
-->
