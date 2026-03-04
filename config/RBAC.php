<?php
/**
 * RBAC (Role-Based Access Control) Class
 * Enterprise-grade permission management system
 */

class RBAC {
    private $db;
    private $userId;
    private $userPermissions = null;
    private $userRoles = null;
    
    public function __construct($db, $userId = null) {
        $this->db = $db;
        $this->userId = $userId;
    }
    
    /**
     * Check if user has a specific permission
     * @param string $permission Permission name (e.g., 'articles.create')
     * @return bool
     */
    public function hasPermission($permission) {
        if (!$this->userId) {
            return false;
        }
        
        if ($this->userPermissions === null) {
            $this->loadUserPermissions();
        }
        
        return in_array($permission, $this->userPermissions);
    }
    
    /**
     * Check if user has any of the specified permissions
     * @param array $permissions Array of permission names
     * @return bool
     */
    public function hasAnyPermission($permissions) {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if user has all specified permissions
     * @param array $permissions Array of permission names
     * @return bool
     */
    public function hasAllPermissions($permissions) {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Check if user has a specific role
     * @param string $roleName Role name (e.g., 'super_admin')
     * @return bool
     */
    public function hasRole($roleName) {
        if (!$this->userId) {
            return false;
        }
        
        if ($this->userRoles === null) {
            $this->loadUserRoles();
        }
        
        return in_array($roleName, $this->userRoles);
    }
    
    /**
     * Check if user has any of the specified roles
     * @param array $roles Array of role names
     * @return bool
     */
    public function hasAnyRole($roles) {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get all user permissions
     * @return array
     */
    public function getUserPermissions() {
        if ($this->userPermissions === null) {
            $this->loadUserPermissions();
        }
        return $this->userPermissions;
    }
    
    /**
     * Get all user roles
     * @return array
     */
    public function getUserRoles() {
        if ($this->userRoles === null) {
            $this->loadUserRoles();
        }
        return $this->userRoles;
    }
    
    /**
     * Load user permissions from database
     */
    private function loadUserPermissions() {
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.name
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            INNER JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = ? 
            AND ur.is_active = 1
            AND (ur.expires_at IS NULL OR ur.expires_at > datetime('now'))
        ");
        $stmt->execute([$this->userId]);
        $this->userPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Load user roles from database
     */
    private function loadUserRoles() {
        $stmt = $this->db->prepare("
            SELECT DISTINCT r.name
            FROM roles r
            INNER JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = ? 
            AND ur.is_active = 1
            AND r.is_active = 1
            AND (ur.expires_at IS NULL OR ur.expires_at > datetime('now'))
        ");
        $stmt->execute([$this->userId]);
        $this->userRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Assign role to user
     * @param int $userId
     * @param string $roleName
     * @param int $assignedBy User ID who is assigning the role
     * @param string|null $expiresAt Optional expiration date
     * @return bool
     */
    public function assignRole($userId, $roleName, $assignedBy, $expiresAt = null) {
        try {
            // Get role ID
            $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = ? AND is_active = 1");
            $stmt->execute([$roleName]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$role) {
                return false;
            }
            
            // Check if already assigned
            $stmt = $this->db->prepare("
                SELECT id FROM user_roles 
                WHERE user_id = ? AND role_id = ?
            ");
            $stmt->execute([$userId, $role['id']]);
            
            if ($stmt->fetch()) {
                // Update existing assignment
                $stmt = $this->db->prepare("
                    UPDATE user_roles 
                    SET is_active = 1, expires_at = ?, assigned_at = datetime('now')
                    WHERE user_id = ? AND role_id = ?
                ");
                $stmt->execute([$expiresAt, $userId, $role['id']]);
            } else {
                // Create new assignment
                $stmt = $this->db->prepare("
                    INSERT INTO user_roles (user_id, role_id, assigned_by, expires_at)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $role['id'], $assignedBy, $expiresAt]);
            }
            
            // Log the action
            $this->logAudit($assignedBy, 'role_assigned', 'user', $userId, json_encode([
                'role' => $roleName,
                'expires_at' => $expiresAt
            ]));
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Revoke role from user
     * @param int $userId
     * @param string $roleName
     * @param int $revokedBy User ID who is revoking the role
     * @return bool
     */
    public function revokeRole($userId, $roleName, $revokedBy) {
        try {
            $stmt = $this->db->prepare("
                UPDATE user_roles 
                SET is_active = 0
                WHERE user_id = ? 
                AND role_id = (SELECT id FROM roles WHERE name = ?)
            ");
            $stmt->execute([$userId, $roleName]);
            
            // Log the action
            $this->logAudit($revokedBy, 'role_revoked', 'user', $userId, json_encode([
                'role' => $roleName
            ]));
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all available roles
     * @return array
     */
    public function getAllRoles() {
        $stmt = $this->db->query("
            SELECT id, name, display_name, description, level
            FROM roles
            WHERE is_active = 1
            ORDER BY level DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all permissions for a role
     * @param string $roleName
     * @return array
     */
    public function getRolePermissions($roleName) {
        $stmt = $this->db->prepare("
            SELECT p.name, p.display_name, p.description, p.resource, p.action
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            INNER JOIN roles r ON rp.role_id = r.id
            WHERE r.name = ?
            ORDER BY p.resource, p.action
        ");
        $stmt->execute([$roleName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user's role details with permissions
     * @param int $userId
     * @return array
     */
    public function getUserRoleDetails($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                r.id, r.name, r.display_name, r.description, r.level,
                ur.assigned_at, ur.expires_at, ur.is_active
            FROM roles r
            INNER JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = ?
            ORDER BY r.level DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if user can perform action on resource
     * @param string $resource Resource name (e.g., 'articles')
     * @param string $action Action name (e.g., 'create', 'update')
     * @return bool
     */
    public function can($resource, $action) {
        return $this->hasPermission("{$resource}.{$action}");
    }
    
    /**
     * Log audit trail
     * @param int $userId
     * @param string $action
     * @param string $resourceType
     * @param int $resourceId
     * @param string $details
     */
    private function logAudit($userId, $action, $resourceType, $resourceId, $details) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO rbac_audit_log 
                (user_id, action, resource_type, resource_id, details, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $action,
                $resourceType,
                $resourceId,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            // Silent fail for audit logging
        }
    }
    
    /**
     * Get audit log for a user
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getAuditLog($userId = null, $limit = 100) {
        if ($userId) {
            $stmt = $this->db->prepare("
                SELECT * FROM rbac_audit_log
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
        } else {
            $stmt = $this->db->prepare("
                SELECT * FROM rbac_audit_log
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Helper function to get RBAC instance for current user
 * @param PDO $db
 * @return RBAC
 */
function getRBAC($db) {
    $userId = $_SESSION['user_id'] ?? null;
    return new RBAC($db, $userId);
}

/**
 * Middleware function to check permission
 * Redirects to access denied page if permission check fails
 * @param PDO $db
 * @param string $permission
 */
function requirePermission($db, $permission) {
    $rbac = getRBAC($db);
    if (!$rbac->hasPermission($permission)) {
        header('HTTP/1.1 403 Forbidden');
        include __DIR__ . '/../admin/access-denied.php';
        exit;
    }
}

/**
 * Middleware function to check role
 * @param PDO $db
 * @param string|array $roles
 */
function requireRole($db, $roles) {
    $rbac = getRBAC($db);
    $roles = is_array($roles) ? $roles : [$roles];
    
    if (!$rbac->hasAnyRole($roles)) {
        header('HTTP/1.1 403 Forbidden');
        include __DIR__ . '/../admin/access-denied.php';
        exit;
    }
}
