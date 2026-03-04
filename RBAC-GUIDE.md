# RBAC Implementation Guide

## Overview

This project now implements enterprise-grade Role-Based Access Control (RBAC) with granular permissions management.

## Architecture

### Core Components

1. **Roles Table** - Defines system roles with hierarchy levels
2. **Permissions Table** - Granular permissions for resources and actions
3. **Role-Permissions Mapping** - Many-to-many relationship
4. **User-Roles Mapping** - Users can have multiple roles
5. **Audit Log** - Tracks all permission changes

### Default Roles

| Role | Level | Description |
|------|-------|-------------|
| Super Admin | 100 | Full system access |
| Content Editor | 50 | Create/edit all content |
| Moderator | 30 | Review submissions, moderate content |
| Registered User | 10 | Basic authenticated access |
| Guest | 0 | Public read-only access |

## Installation

### Step 1: Run Migration

```bash
php migrate-to-rbac.php
```

This will:
- Create RBAC tables
- Insert default roles and permissions
- Migrate existing users to new system
- Create audit log table

### Step 2: Verify Installation

Check that these tables exist:
- `roles`
- `permissions`
- `role_permissions`
- `user_roles`
- `rbac_audit_log`

## Usage

### Basic Permission Check

```php
<?php
require_once 'config/RBAC.php';

$db = getDBConnection();
$rbac = getRBAC($db);

// Check single permission
if ($rbac->hasPermission('articles.create')) {
    // User can create articles
}

// Check resource and action
if ($rbac->can('articles', 'update')) {
    // User can update articles
}
```

### Protecting Admin Pages

```php
<?php
session_start();
require_once '../config/database.php';
require_once '../config/RBAC.php';

$db = getDBConnection();

// Require specific permission
requirePermission($db, 'articles.create');

// OR require specific role
requireRole($db, 'super_admin');

// OR require any of multiple roles
requireRole($db, ['super_admin', 'content_editor']);
```

### Role Management

```php
<?php
$rbac = getRBAC($db);

// Assign role to user
$rbac->assignRole(
    $userId, 
    'content_editor', 
    $_SESSION['user_id'], // who is assigning
    '2026-12-31 23:59:59' // optional expiration
);

// Revoke role
$rbac->revokeRole($userId, 'content_editor', $_SESSION['user_id']);

// Check if user has role
if ($rbac->hasRole('super_admin')) {
    // User is super admin
}

// Get all user roles
$roles = $rbac->getUserRoles();

// Get all user permissions
$permissions = $rbac->getUserPermissions();
```

### Advanced Checks

```php
<?php
// Check multiple permissions (ANY)
if ($rbac->hasAnyPermission(['articles.create', 'articles.update'])) {
    // User can create OR update
}

// Check multiple permissions (ALL)
if ($rbac->hasAllPermissions(['articles.create', 'articles.publish'])) {
    // User can both create AND publish
}

// Check multiple roles
if ($rbac->hasAnyRole(['super_admin', 'moderator'])) {
    // User is admin or moderator
}
```

## Permission Naming Convention

Format: `{resource}.{action}`

### Resources
- articles
- dictionary
- lessons
- grammar
- media
- proverbs
- translations
- users
- messages
- system

### Actions
- create
- read
- update
- delete
- publish (for articles)
- manage_roles (for users)
- settings (for system)
- backup (for system)
- logs (for system)

### Examples
- `articles.create` - Can create articles
- `users.manage_roles` - Can assign roles to users
- `system.settings` - Can modify system settings

## UI Integration

### Show/Hide Based on Permissions

```php
<?php if ($rbac->hasPermission('articles.create')): ?>
    <a href="articles-manage.php?action=new">Create Article</a>
<?php endif; ?>

<?php if ($rbac->hasRole('super_admin')): ?>
    <a href="roles-manage.php">Manage Roles</a>
<?php endif; ?>
```

### Dynamic Menu Generation

```php
<?php
$menuItems = [
    ['label' => 'Articles', 'url' => 'articles-manage.php', 'permission' => 'articles.read'],
    ['label' => 'Users', 'url' => 'users-manage.php', 'permission' => 'users.read'],
    ['label' => 'Roles', 'url' => 'roles-manage.php', 'role' => 'super_admin'],
];

foreach ($menuItems as $item) {
    $canAccess = false;
    
    if (isset($item['permission'])) {
        $canAccess = $rbac->hasPermission($item['permission']);
    } elseif (isset($item['role'])) {
        $canAccess = $rbac->hasRole($item['role']);
    }
    
    if ($canAccess) {
        echo "<a href='{$item['url']}'>{$item['label']}</a>";
    }
}
?>
```

## Admin Interface

### Role Management Page

Access: `admin/roles-manage.php`

Features:
- View all roles and their levels
- Assign roles to users
- Set role expiration dates
- View current role assignments
- Revoke roles

Requirements: `super_admin` role

## Audit Trail

All role assignments and revocations are logged:

```php
<?php
// Get audit log for specific user
$logs = $rbac->getAuditLog($userId, 50);

// Get all audit logs
$logs = $rbac->getAuditLog(null, 100);

foreach ($logs as $log) {
    echo "{$log['action']} at {$log['created_at']}";
}
?>
```

## Security Best Practices

1. **Always check permissions** before displaying sensitive data or forms
2. **Use requirePermission()** at the top of admin pages
3. **Never trust client-side checks** - always validate server-side
4. **Log sensitive operations** using the audit trail
5. **Review role assignments** regularly
6. **Use temporary roles** for time-limited access
7. **Principle of least privilege** - assign minimum necessary permissions

## Migration from Old System

The migration script automatically:
- Converts `admin` users → `super_admin` role
- Converts `editor` users → `content_editor` role
- Preserves the old `role` column for backward compatibility

Old code using `$_SESSION['role']` will still work, but should be updated to use RBAC.

## Extending the System

### Adding New Permissions

```sql
INSERT INTO permissions (name, display_name, description, resource, action) 
VALUES ('blog.moderate', 'Moderate Blog', 'Can moderate blog comments', 'blog', 'moderate');
```

### Creating Custom Roles

```sql
INSERT INTO roles (name, display_name, description, level) 
VALUES ('translator', 'Translator', 'Can manage translations only', 40);

-- Assign specific permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'translator' 
AND p.resource = 'translations';
```

### Adding Permissions to Existing Roles

```sql
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'moderator' 
AND p.name = 'blog.moderate';
```

## Troubleshooting

### User has no permissions

Check:
1. User has active role assignment: `SELECT * FROM user_roles WHERE user_id = ?`
2. Role has permissions: `SELECT * FROM role_permissions WHERE role_id = ?`
3. Role is active: `SELECT * FROM roles WHERE id = ? AND is_active = 1`
4. Assignment hasn't expired: Check `expires_at` column

### Permission check always fails

Ensure:
1. RBAC.php is included
2. Session contains `user_id`
3. Database connection is active
4. Permission name is correct (case-sensitive)

### Access denied page not showing

Check:
1. `admin/access-denied.php` exists
2. Headers not already sent
3. Correct path in `requirePermission()` function

## Performance Optimization

Permissions are cached per request in the RBAC class. For better performance:

1. **Load once per request** - RBAC automatically caches
2. **Use indexes** - Already created by migration script
3. **Avoid repeated checks** - Store result in variable if checking multiple times

```php
<?php
// Good - check once
$canEdit = $rbac->hasPermission('articles.update');
if ($canEdit) { /* ... */ }
if ($canEdit) { /* ... */ }

// Avoid - checks twice
if ($rbac->hasPermission('articles.update')) { /* ... */ }
if ($rbac->hasPermission('articles.update')) { /* ... */ }
?>
```

## API Integration

For API endpoints, check permissions before processing:

```php
<?php
session_start();
require_once '../config/database.php';
require_once '../config/RBAC.php';

$db = getDBConnection();
$rbac = getRBAC($db);

if (!$rbac->hasPermission('articles.create')) {
    http_response_code(403);
    echo json_encode(['error' => 'Insufficient permissions']);
    exit;
}

// Process request
?>
```

## Testing

Test different role scenarios:

1. Create test users with different roles
2. Verify each role has correct permissions
3. Test permission inheritance
4. Test role expiration
5. Verify audit logging

## Support

For issues or questions:
1. Check audit logs for permission changes
2. Verify database schema matches rbac-setup.sql
3. Review RBAC.php for available methods
4. Check session data for user_id
