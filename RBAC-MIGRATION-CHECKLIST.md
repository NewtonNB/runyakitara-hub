# RBAC Migration Checklist

Use this checklist to update your existing admin pages to use RBAC.

## ✅ Initial Setup (One-time)

- [ ] Run `setup-rbac.bat` (Windows) or `./setup-rbac.sh` (Linux/Mac)
- [ ] Verify migration completed successfully
- [ ] Check that RBAC tables exist in database
- [ ] Test login with existing admin account
- [ ] Access `admin/roles-manage.php` to verify super_admin role

## 📝 For Each Admin Page

### 1. Add Required Includes

```php
// Add after session_start() and database connection
require_once '../config/RBAC.php';
require_once 'includes/rbac-helpers.php';  // Optional but recommended
```

- [ ] Added RBAC.php include
- [ ] Added rbac-helpers.php include (optional)

### 2. Initialize RBAC

```php
$db = getDBConnection();
$rbac = getRBAC($db);
```

- [ ] Created RBAC instance

### 3. Add Page-Level Protection

```php
// Require minimum permission to view page
requirePermission($db, 'resource.read');

// OR require specific role
requireRole($db, 'super_admin');
```

- [ ] Added page-level permission check
- [ ] Tested access with different user roles

### 4. Protect Form Actions

Update POST handlers:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        if (!$rbac->hasPermission('resource.create')) {
            $error = 'Permission denied';
        } else {
            // Process creation
        }
    }
    
    if ($action === 'update') {
        if (!$rbac->hasPermission('resource.update')) {
            $error = 'Permission denied';
        } else {
            // Process update
        }
    }
    
    if ($action === 'delete') {
        if (!$rbac->hasPermission('resource.delete')) {
            $error = 'Permission denied';
        } else {
            // Process deletion
        }
    }
}
```

- [ ] Protected CREATE actions
- [ ] Protected UPDATE actions
- [ ] Protected DELETE actions
- [ ] Added error messages for denied actions

### 5. Update UI Elements

#### Hide/Show Buttons

```php
<?php if ($rbac->hasPermission('resource.create')): ?>
    <a href="create.php" class="btn">Create New</a>
<?php endif; ?>
```

- [ ] Wrapped create buttons in permission checks
- [ ] Wrapped edit buttons in permission checks
- [ ] Wrapped delete buttons in permission checks

#### Table Actions

```php
<?php if ($rbac->can('resource', 'update')): ?>
    <a href="edit.php?id=<?php echo $id; ?>">Edit</a>
<?php endif; ?>

<?php if ($rbac->can('resource', 'delete')): ?>
    <button onclick="deleteItem(<?php echo $id; ?>)">Delete</button>
<?php endif; ?>
```

- [ ] Updated table action buttons
- [ ] Tested with different permission levels

### 6. Test Different Scenarios

- [ ] Test as Super Admin (should have full access)
- [ ] Test as Content Editor (should have content access)
- [ ] Test as Moderator (should have limited access)
- [ ] Test as user with no permissions (should see access denied)
- [ ] Verify error messages display correctly
- [ ] Check that hidden buttons don't appear

## 📋 Page-by-Page Migration

### Articles Management (`admin/articles-manage.php`)
- [ ] Added RBAC includes
- [ ] Protected with `articles.read`
- [ ] Protected create action with `articles.create`
- [ ] Protected update action with `articles.update`
- [ ] Protected delete action with `articles.delete`
- [ ] Updated UI elements
- [ ] Tested all scenarios

### Dictionary Management (`admin/dictionary-manage.php`)
- [ ] Added RBAC includes
- [ ] Protected with `dictionary.read`
- [ ] Protected create action with `dictionary.create`
- [ ] Protected update action with `dictionary.update`
- [ ] Protected delete action with `dictionary.delete`
- [ ] Updated UI elements
- [ ] Tested all scenarios

### Lessons Management (`admin/lessons-manage.php`)
- [ ] Added RBAC includes
- [ ] Protected with `lessons.read`
- [ ] Protected create action with `lessons.create`
- [ ] Protected update action with `lessons.update`
- [ ] Protected delete action with `lessons.delete`
- [ ] Updated UI elements
- [ ] Tested all scenarios

### Grammar Management (`admin/grammar-manage.php`)
- [ ] Added RBAC includes
- [ ] Protected with `grammar.read`
- [ ] Protected create action with `grammar.create`
- [ ] Protected update action with `grammar.update`
- [ ] Protected delete action with `grammar.delete`
- [ ] Updated UI elements
- [ ] Tested all scenarios

### Media Management (`admin/media-manage.php`)
- [ ] Added RBAC includes
- [ ] Protected with `media.read`
- [ ] Protected create action with `media.create`
- [ ] Protected update action with `media.update`
- [ ] Protected delete action with `media.delete`
- [ ] Updated UI elements
- [ ] Tested all scenarios

### Proverbs Management (`admin/proverbs-manage.php`)
- [ ] Added RBAC includes
- [ ] Protected with `proverbs.read`
- [ ] Protected create action with `proverbs.create`
- [ ] Protected update action with `proverbs.update`
- [ ] Protected delete action with `proverbs.delete`
- [ ] Updated UI elements
- [ ] Tested all scenarios

### Translations Management (`admin/translations-manage.php`)
- [ ] Added RBAC includes
- [ ] Protected with `translations.read`
- [ ] Protected create action with `translations.create`
- [ ] Protected update action with `translations.update`
- [ ] Protected delete action with `translations.delete`
- [ ] Updated UI elements
- [ ] Tested all scenarios

### Users Management (`admin/users-manage.php`)
- [ ] Added RBAC includes
- [ ] Protected with `users.read`
- [ ] Protected create action with `users.create`
- [ ] Protected update action with `users.update`
- [ ] Protected delete action with `users.delete`
- [ ] Protected role management with `users.manage_roles`
- [ ] Updated UI elements
- [ ] Tested all scenarios

### Messages Management (`admin/messages-manage.php`)
- [ ] Added RBAC includes
- [ ] Protected with `messages.read`
- [ ] Protected update action with `messages.update`
- [ ] Protected delete action with `messages.delete`
- [ ] Updated UI elements
- [ ] Tested all scenarios

### Dashboard (`admin/dashboard-new.php`)
- [ ] Added RBAC includes
- [ ] Show/hide widgets based on permissions
- [ ] Display user's current roles
- [ ] Show permission-based statistics
- [ ] Tested with different roles

## 🔍 Verification Steps

### Database Verification
```sql
-- Check roles exist
SELECT * FROM roles;

-- Check permissions exist
SELECT * FROM permissions;

-- Check role-permission mappings
SELECT r.name, p.name 
FROM roles r
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
ORDER BY r.level DESC, p.resource, p.action;

-- Check user role assignments
SELECT u.username, r.name, ur.is_active
FROM users u
JOIN user_roles ur ON u.id = ur.user_id
JOIN roles r ON ur.role_id = r.id;
```

- [ ] All roles present
- [ ] All permissions present
- [ ] Role-permission mappings correct
- [ ] User role assignments correct

### Functional Testing
- [ ] Login as super_admin - verify full access
- [ ] Login as content_editor - verify content access only
- [ ] Login as moderator - verify limited access
- [ ] Try accessing protected pages without permission
- [ ] Verify access denied page displays correctly
- [ ] Check audit log records actions

### UI Testing
- [ ] Buttons appear/disappear based on permissions
- [ ] Navigation items show/hide correctly
- [ ] Role badge displays in sidebar
- [ ] Permission badges work in role management
- [ ] No console errors

## 📚 Reference Files

Keep these files handy during migration:

- [ ] `RBAC-GUIDE.md` - Full documentation
- [ ] `RBAC-QUICK-REFERENCE.md` - Quick reference
- [ ] `admin/example-rbac-usage.php` - Working example
- [ ] `config/RBAC.php` - Core class reference
- [ ] `admin/includes/rbac-helpers.php` - Helper functions

## 🎯 Post-Migration Tasks

- [ ] Update documentation with RBAC info
- [ ] Train team on new permission system
- [ ] Review and adjust default role permissions
- [ ] Create custom roles if needed
- [ ] Set up role expiration for temporary access
- [ ] Review audit logs regularly
- [ ] Change default admin password
- [ ] Test backup and restore procedures

## 🐛 Common Issues & Solutions

### Issue: User has no permissions after migration
**Solution:** Check user_roles table, ensure role is active and not expired

### Issue: Permission check always fails
**Solution:** Verify permission name is correct (case-sensitive), check role has permission

### Issue: Access denied page not showing
**Solution:** Check admin/access-denied.php exists, verify headers not sent

### Issue: Old role column conflicts
**Solution:** Old 'role' column kept for backward compatibility, RBAC takes precedence

### Issue: Session doesn't have user_roles
**Solution:** User needs to log out and log back in after migration

## ✨ Best Practices

- [ ] Always check permissions server-side (never trust client-side)
- [ ] Use requirePermission() at top of pages
- [ ] Check permissions before processing forms
- [ ] Hide UI elements user can't use
- [ ] Provide clear error messages
- [ ] Log sensitive operations
- [ ] Use principle of least privilege
- [ ] Review permissions regularly
- [ ] Test with multiple user types
- [ ] Document custom roles and permissions

## 📊 Progress Tracking

Total Pages: _____ / _____
Completed: _____ / _____
Tested: _____ / _____
Deployed: _____ / _____

## Notes

Use this space for migration notes, issues encountered, or custom modifications:

```
[Your notes here]
```

---

**Migration Complete?** 🎉

Once all checkboxes are complete:
1. Do final testing with all user roles
2. Review audit logs
3. Update team documentation
4. Deploy to production
5. Monitor for issues
