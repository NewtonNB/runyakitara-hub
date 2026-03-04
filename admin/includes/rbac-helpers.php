<?php
/**
 * RBAC Helper Functions for Admin Pages
 * Include this file to easily add permission checks to your pages
 */

require_once __DIR__ . '/../../config/RBAC.php';

/**
 * Get RBAC instance for current session
 * @return RBAC|null
 */
function getSessionRBAC() {
    global $db;
    if (!isset($db)) {
        require_once __DIR__ . '/../../config/database.php';
        $db = getDBConnection();
    }
    return getRBAC($db);
}

/**
 * Check if current user can perform action
 * @param string $resource
 * @param string $action
 * @return bool
 */
function canDo($resource, $action) {
    $rbac = getSessionRBAC();
    return $rbac->can($resource, $action);
}

/**
 * Render action button with permission check
 * @param string $permission Permission required
 * @param string $label Button label
 * @param string $url Button URL
 * @param string $icon Bootstrap icon class
 * @param string $class Additional CSS classes
 */
function renderActionButton($permission, $label, $url, $icon = '', $class = 'btn-primary') {
    $rbac = getSessionRBAC();
    if ($rbac->hasPermission($permission)) {
        $iconHtml = $icon ? "<i class='bi bi-{$icon}'></i> " : '';
        echo "<a href='{$url}' class='btn {$class}'>{$iconHtml}{$label}</a>";
    }
}

/**
 * Render conditional content based on permission
 * @param string $permission
 * @param callable $callback Function to execute if permission granted
 */
function ifCan($permission, $callback) {
    $rbac = getSessionRBAC();
    if ($rbac->hasPermission($permission)) {
        $callback();
    }
}

/**
 * Render conditional content based on role
 * @param string|array $roles
 * @param callable $callback
 */
function ifRole($roles, $callback) {
    $rbac = getSessionRBAC();
    $roles = is_array($roles) ? $roles : [$roles];
    if ($rbac->hasAnyRole($roles)) {
        $callback();
    }
}

/**
 * Get user's role display names
 * @return array
 */
function getUserRoleNames() {
    global $db;
    if (!isset($_SESSION['user_id'])) {
        return [];
    }
    
    $stmt = $db->prepare("
        SELECT r.display_name
        FROM roles r
        INNER JOIN user_roles ur ON r.id = ur.role_id
        WHERE ur.user_id = ? AND ur.is_active = 1
        ORDER BY r.level DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Check if user is super admin
 * @return bool
 */
function isSuperAdmin() {
    $rbac = getSessionRBAC();
    return $rbac->hasRole('super_admin');
}

/**
 * Render permission-based table actions
 * @param int $itemId
 * @param string $resource Resource name (e.g., 'articles')
 * @param string $editUrl Edit page URL
 * @param string $deleteAction Delete form action
 */
function renderTableActions($itemId, $resource, $editUrl, $deleteAction) {
    $rbac = getSessionRBAC();
    
    echo '<div class="table-actions">';
    
    if ($rbac->can($resource, 'update')) {
        echo "<a href='{$editUrl}?id={$itemId}' class='btn-icon' title='Edit'>
                <i class='bi bi-pencil'></i>
              </a>";
    }
    
    if ($rbac->can($resource, 'delete')) {
        echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure?\")'>
                <input type='hidden' name='action' value='delete'>
                <input type='hidden' name='id' value='{$itemId}'>
                <button type='submit' class='btn-icon btn-danger' title='Delete'>
                    <i class='bi bi-trash'></i>
                </button>
              </form>";
    }
    
    echo '</div>';
}

/**
 * Display permission denied message
 * @param string $action Action that was denied
 */
function showPermissionDenied($action = 'perform this action') {
    echo "<div class='alert alert-error'>
            <i class='bi bi-shield-x'></i>
            You don't have permission to {$action}.
          </div>";
}

/**
 * Get permission badge HTML
 * @param string $permission
 * @return string
 */
function getPermissionBadge($permission) {
    $rbac = getSessionRBAC();
    if ($rbac->hasPermission($permission)) {
        return "<span class='badge badge-success'><i class='bi bi-check-circle'></i> Granted</span>";
    }
    return "<span class='badge badge-secondary'><i class='bi bi-x-circle'></i> Denied</span>";
}

/**
 * Check multiple permissions and return results
 * @param array $permissions
 * @return array Associative array of permission => bool
 */
function checkPermissions($permissions) {
    $rbac = getSessionRBAC();
    $results = [];
    foreach ($permissions as $permission) {
        $results[$permission] = $rbac->hasPermission($permission);
    }
    return $results;
}

/**
 * Render navigation item with permission check
 * @param string $permission
 * @param string $url
 * @param string $label
 * @param string $icon
 * @param bool $isActive
 */
function renderNavItem($permission, $url, $label, $icon, $isActive = false) {
    $rbac = getSessionRBAC();
    if ($rbac->hasPermission($permission)) {
        $activeClass = $isActive ? 'active' : '';
        echo "<a href='{$url}' class='nav-item {$activeClass}'>
                <i class='bi bi-{$icon}'></i>
                <span>{$label}</span>
              </a>";
    }
}
