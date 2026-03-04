# RBAC Quick Reference Card

## Setup (One-time)

```bash
# Windows
setup-rbac.bat

# Linux/Mac
chmod +x setup-rbac.sh
./setup-rbac.sh
```

## Basic Page Protection

```php
<?php
session_start();
require_once '../config/database.php';
require_once '../config/RBAC.php';

$db = getDBConnection();

// Require permission to access page
requirePermission($db, 'articles.read');

// OR require specific role
requireRole($db, 'super_admin');

// OR require any of multiple roles
requireRole($db, ['super_admin', 'content_editor']);
?>
```

## Permission Checks

```php
<?php
$rbac = getRBAC($db);

// Single permission
if ($rbac->hasPermission('articles.create')) {
    // User can create articles
}

// Resource + Action
if ($rbac->can('articles', 'update')) {
    // User can update articles
}

// Multiple permissions (ANY)
if ($rbac->hasAnyPermission(['articles.create', 'articles.update'])) {
    // User has at least one permission
}

// Multiple permissions (ALL)
if ($rbac->hasAllPermissions(['articles.create', 'articles.publish'])) {
    // User has all permissions
}
?>
```

## Role Checks

```php
<?php
// Single role
if ($rbac->hasRole('super_admin')) {
    // User is super admin
}

// Multiple roles
if ($rbac->hasAnyRole(['super_admin', 'moderator'])) {
    // User has at least one role
}
?>
```

## Using Helper Functions

```php
<?php
require_once 'includes/rbac-helpers.php';

// Quick permission check
if (canDo('articles', 'create')) {
    // Can create articles
}

// Render button with permission check
renderActionButton('articles.create', 'New Article', 'create.php', 'plus-circle');

// Conditional rendering
ifCan('articles.delete', function() {
    echo '<button>Delete</button>';
});

// Role-based rendering
ifRole('super_admin', function() {
    echo '<a href="admin-panel.php">Admin Panel</a>';
});

// Check if super admin
if (isSuperAdmin()) {
    // Super admin only code
}
?>
```

## Form Action Protection

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        if (!$rbac->hasPermission('articles.create')) {
            $error = 'Permission denied';
        } else {
            // Process creation
        }
    }
    
    if ($action === 'delete') {
        if (!$rbac->hasPermission('articles.delete')) {
            $error = 'Permission denied';
        } else {
            // Process deletion
        }
    }
}
?>
```

## UI Conditional Display

```php
<!-- Show/hide buttons -->
<?php if ($rbac->hasPermission('articles.create')): ?>
    <a href="create.php" class="btn">Create Article</a>
<?php endif; ?>

<!-- Show/hide sections -->
<?php if ($rbac->hasRole('super_admin')): ?>
    <div class="admin-section">
        <!-- Admin only content -->
    </div>
<?php endif; ?>

<!-- Table actions -->
<td>
    <?php if ($rbac->can('articles', 'update')): ?>
        <a href="edit.php?id=<?php echo $id; ?>">Edit</a>
    <?php endif; ?>
    
    <?php if ($rbac->can('articles', 'delete')): ?>
        <button onclick="deleteItem(<?php echo $id; ?>)">Delete</button>
    <?php endif; ?>
</td>
```

## Role Management

```php
<?php
$rbac = getRBAC($db);

// Assign role to user
$rbac->assignRole(
    $userId,                    // User ID
    'content_editor',           // Role name
    $_SESSION['user_id'],       // Who is assigning
    '2026-12-31 23:59:59'      // Optional expiration
);

// Revoke role
$rbac->revokeRole($userId, 'content_editor', $_SESSION['user_id']);

// Get user's roles
$roles = $rbac->getUserRoles();

// Get user's permissions
$permissions = $rbac->getUserPermissions();

// Get all available roles
$allRoles = $rbac->getAllRoles();
?>
```

## Permission Names

Format: `{resource}.{action}`

### Common Permissions

```
articles.create
articles.read
articles.update
articles.delete
articles.publish

dictionary.create
dictionary.read
dictionary.update
dictionary.delete

lessons.create
lessons.read
lessons.update
lessons.delete

users.create
users.read
users.update
users.delete
users.manage_roles

messages.read
messages.update
messages.delete

system.settings
system.backup
system.logs
```

## Default Roles

| Role | Level | Description |
|------|-------|-------------|
| super_admin | 100 | All permissions |
| content_editor | 50 | Full content management |
| moderator | 30 | Review and moderate |
| registered_user | 10 | Basic access |
| guest | 0 | Public read-only |

## Session Variables

After login, these are available:

```php
$_SESSION['user_id']           // User ID
$_SESSION['username']          // Username
$_SESSION['user_roles']        // Array of role names
$_SESSION['user_permissions']  // Array of permission names
```

## Common Patterns

### Protect entire page
```php
requirePermission($db, 'articles.read');
```

### Protect specific action
```php
if (!$rbac->hasPermission('articles.delete')) {
    die('Access denied');
}
```

### Show admin menu item
```php
<?php if ($rbac->hasRole('super_admin')): ?>
    <a href="roles-manage.php">Manage Roles</a>
<?php endif; ?>
```

### Check before form submission
```php
if ($_POST && !$rbac->hasPermission('articles.create')) {
    $error = 'You cannot create articles';
}
```

## Troubleshooting

### User has no permissions
```sql
-- Check user roles
SELECT * FROM user_roles WHERE user_id = ?;

-- Check role permissions
SELECT * FROM role_permissions WHERE role_id = ?;
```

### Permission check fails
1. Verify permission name is correct (case-sensitive)
2. Check user has active role assignment
3. Ensure role has the permission
4. Check role expiration date

### Access denied page not showing
1. Ensure `admin/access-denied.php` exists
2. Check headers not already sent
3. Verify path in `requirePermission()` function

## Files Reference

- `config/rbac-setup.sql` - Database schema
- `config/RBAC.php` - Core RBAC class
- `admin/includes/rbac-helpers.php` - Helper functions
- `admin/roles-manage.php` - Role management UI
- `admin/access-denied.php` - Access denied page
- `migrate-to-rbac.php` - Migration script
- `RBAC-GUIDE.md` - Full documentation
- `admin/example-rbac-usage.php` - Complete example

## Need Help?

1. Read `RBAC-GUIDE.md` for detailed documentation
2. Check `admin/example-rbac-usage.php` for working examples
3. Review audit logs: `SELECT * FROM rbac_audit_log`
4. Verify user permissions: `$rbac->getUserPermissions()`
