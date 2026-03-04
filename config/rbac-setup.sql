-- ============================================
-- RBAC (Role-Based Access Control) Schema
-- Enterprise-grade permission management
-- ============================================

-- Roles table: Define system roles
CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    display_name TEXT NOT NULL,
    description TEXT,
    level INTEGER NOT NULL DEFAULT 0, -- Hierarchy level (higher = more privileges)
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Permissions table: Define granular permissions
CREATE TABLE IF NOT EXISTS permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    display_name TEXT NOT NULL,
    description TEXT,
    resource TEXT NOT NULL, -- e.g., 'articles', 'users', 'dictionary'
    action TEXT NOT NULL, -- e.g., 'create', 'read', 'update', 'delete'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Role-Permission mapping: Many-to-many relationship
CREATE TABLE IF NOT EXISTS role_permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_id INTEGER NOT NULL,
    permission_id INTEGER NOT NULL,
    granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    granted_by INTEGER, -- user_id who granted this permission
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id),
    UNIQUE(role_id, permission_id)
);

-- User-Role mapping: Many-to-many relationship (users can have multiple roles)
CREATE TABLE IF NOT EXISTS user_roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    assigned_by INTEGER, -- user_id who assigned this role
    expires_at DATETIME, -- Optional: for temporary role assignments
    is_active INTEGER DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    UNIQUE(user_id, role_id)
);

-- Update users table to remove old role column (will be handled by user_roles)
-- Keep it for backward compatibility but mark as deprecated

-- ============================================
-- Insert Default Roles
-- ============================================

INSERT INTO roles (name, display_name, description, level) VALUES
('super_admin', 'Super Administrator', 'Full system access with all permissions', 100),
('content_editor', 'Content Editor', 'Can create and edit content (articles, lessons, dictionary)', 50),
('moderator', 'Moderator', 'Can review and moderate user submissions and messages', 30),
('registered_user', 'Registered User', 'Authenticated user with basic access', 10),
('guest', 'Guest', 'Public access with read-only permissions', 0);

-- ============================================
-- Insert Permissions (CRUD for each resource)
-- ============================================

-- Articles permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('articles.create', 'Create Articles', 'Can create new articles', 'articles', 'create'),
('articles.read', 'Read Articles', 'Can view articles', 'articles', 'read'),
('articles.update', 'Update Articles', 'Can edit existing articles', 'articles', 'update'),
('articles.delete', 'Delete Articles', 'Can delete articles', 'articles', 'delete'),
('articles.publish', 'Publish Articles', 'Can publish/unpublish articles', 'articles', 'publish');

-- Dictionary permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('dictionary.create', 'Create Dictionary Entries', 'Can add new words', 'dictionary', 'create'),
('dictionary.read', 'Read Dictionary', 'Can view dictionary entries', 'dictionary', 'read'),
('dictionary.update', 'Update Dictionary', 'Can edit dictionary entries', 'dictionary', 'update'),
('dictionary.delete', 'Delete Dictionary', 'Can remove dictionary entries', 'dictionary', 'delete');

-- Lessons permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('lessons.create', 'Create Lessons', 'Can create new lessons', 'lessons', 'create'),
('lessons.read', 'Read Lessons', 'Can view lessons', 'lessons', 'read'),
('lessons.update', 'Update Lessons', 'Can edit lessons', 'lessons', 'update'),
('lessons.delete', 'Delete Lessons', 'Can delete lessons', 'lessons', 'delete');

-- Grammar permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('grammar.create', 'Create Grammar Topics', 'Can add grammar content', 'grammar', 'create'),
('grammar.read', 'Read Grammar', 'Can view grammar topics', 'grammar', 'read'),
('grammar.update', 'Update Grammar', 'Can edit grammar topics', 'grammar', 'update'),
('grammar.delete', 'Delete Grammar', 'Can remove grammar topics', 'grammar', 'delete');

-- Media permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('media.create', 'Upload Media', 'Can upload audio/video files', 'media', 'create'),
('media.read', 'View Media', 'Can view media files', 'media', 'read'),
('media.update', 'Update Media', 'Can edit media metadata', 'media', 'update'),
('media.delete', 'Delete Media', 'Can remove media files', 'media', 'delete');

-- Proverbs permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('proverbs.create', 'Create Proverbs', 'Can add new proverbs', 'proverbs', 'create'),
('proverbs.read', 'Read Proverbs', 'Can view proverbs', 'proverbs', 'read'),
('proverbs.update', 'Update Proverbs', 'Can edit proverbs', 'proverbs', 'update'),
('proverbs.delete', 'Delete Proverbs', 'Can remove proverbs', 'proverbs', 'delete');

-- Translations permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('translations.create', 'Create Translations', 'Can add translations', 'translations', 'create'),
('translations.read', 'Read Translations', 'Can view translations', 'translations', 'read'),
('translations.update', 'Update Translations', 'Can edit translations', 'translations', 'update'),
('translations.delete', 'Delete Translations', 'Can remove translations', 'translations', 'delete');

-- User management permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('users.create', 'Create Users', 'Can create new user accounts', 'users', 'create'),
('users.read', 'View Users', 'Can view user information', 'users', 'read'),
('users.update', 'Update Users', 'Can edit user accounts', 'users', 'update'),
('users.delete', 'Delete Users', 'Can remove user accounts', 'users', 'delete'),
('users.manage_roles', 'Manage User Roles', 'Can assign/revoke user roles', 'users', 'manage_roles');

-- Messages permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('messages.read', 'Read Messages', 'Can view contact messages', 'messages', 'read'),
('messages.update', 'Update Messages', 'Can mark messages as read/replied', 'messages', 'update'),
('messages.delete', 'Delete Messages', 'Can delete messages', 'messages', 'delete');

-- System permissions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('system.settings', 'System Settings', 'Can modify system settings', 'system', 'settings'),
('system.backup', 'System Backup', 'Can create/restore backups', 'system', 'backup'),
('system.logs', 'View Logs', 'Can view system logs', 'system', 'logs');

-- ============================================
-- Assign Permissions to Roles
-- ============================================

-- Super Admin: ALL permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'super_admin';

-- Content Editor: Full CRUD on content, no user/system management
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'content_editor'
AND p.resource IN ('articles', 'dictionary', 'lessons', 'grammar', 'media', 'proverbs', 'translations');

-- Moderator: Read all content, manage messages, limited edit rights
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'moderator'
AND (
    p.action = 'read' 
    OR p.resource = 'messages'
    OR (p.resource IN ('articles', 'proverbs', 'translations') AND p.action = 'update')
);

-- Registered User: Read-only access to most content
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'registered_user'
AND p.action = 'read'
AND p.resource NOT IN ('users', 'system', 'messages');

-- Guest: Public read-only access
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'guest'
AND p.action = 'read'
AND p.resource IN ('articles', 'dictionary', 'lessons', 'grammar', 'media', 'proverbs', 'translations');

-- ============================================
-- Migrate existing users to new RBAC system
-- ============================================

-- Assign super_admin role to existing admin users
INSERT INTO user_roles (user_id, role_id, assigned_by)
SELECT u.id, r.id, u.id
FROM users u, roles r
WHERE u.role = 'admin' AND r.name = 'super_admin';

-- Assign content_editor role to existing editor users
INSERT INTO user_roles (user_id, role_id, assigned_by)
SELECT u.id, r.id, u.id
FROM users u, roles r
WHERE u.role = 'editor' AND r.name = 'content_editor';

-- ============================================
-- Create indexes for performance
-- ============================================

CREATE INDEX IF NOT EXISTS idx_role_permissions_role ON role_permissions(role_id);
CREATE INDEX IF NOT EXISTS idx_role_permissions_permission ON role_permissions(permission_id);
CREATE INDEX IF NOT EXISTS idx_user_roles_user ON user_roles(user_id);
CREATE INDEX IF NOT EXISTS idx_user_roles_role ON user_roles(role_id);
CREATE INDEX IF NOT EXISTS idx_permissions_resource ON permissions(resource);
CREATE INDEX IF NOT EXISTS idx_permissions_action ON permissions(action);

-- ============================================
-- Audit log table (optional but recommended)
-- ============================================

CREATE TABLE IF NOT EXISTS rbac_audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    action TEXT NOT NULL, -- 'role_assigned', 'role_revoked', 'permission_granted', etc.
    resource_type TEXT, -- 'user', 'role', 'permission'
    resource_id INTEGER,
    details TEXT, -- JSON string with additional info
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_audit_user ON rbac_audit_log(user_id);
CREATE INDEX IF NOT EXISTS idx_audit_action ON rbac_audit_log(action);
CREATE INDEX IF NOT EXISTS idx_audit_created ON rbac_audit_log(created_at);
