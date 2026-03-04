<?php
/**
 * RBAC Migration Script
 * Migrates the database to use Role-Based Access Control
 */

require_once 'config/database.php';

echo "===========================================\n";
echo "RBAC Migration Script\n";
echo "===========================================\n\n";

try {
    $db = getDBConnection();
    
    echo "✓ Database connection established\n\n";
    
    // Read and execute RBAC setup SQL
    $sqlFile = __DIR__ . '/config/rbac-setup.sql';
    
    if (!file_exists($sqlFile)) {
        die("✗ Error: rbac-setup.sql not found!\n");
    }
    
    echo "Reading RBAC schema...\n";
    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt);
        }
    );
    
    echo "Executing " . count($statements) . " SQL statements...\n\n";
    
    $db->beginTransaction();
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            $db->exec($statement);
            $successCount++;
            
            // Show progress for major operations
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?(\w+)/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                echo "  ✓ Created table: $tableName\n";
            } elseif (stripos($statement, 'INSERT INTO roles') !== false) {
                echo "  ✓ Inserted default roles\n";
            } elseif (stripos($statement, 'INSERT INTO permissions') !== false && $index % 5 == 0) {
                echo "  ✓ Inserting permissions...\n";
            }
        } catch (PDOException $e) {
            // Ignore "already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errorCount++;
                echo "  ✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    $db->commit();
    
    echo "\n===========================================\n";
    echo "Migration Summary\n";
    echo "===========================================\n";
    echo "Successful operations: $successCount\n";
    echo "Errors (non-critical): $errorCount\n\n";
    
    // Verify the migration
    echo "Verifying RBAC tables...\n";
    
    $tables = ['roles', 'permissions', 'role_permissions', 'user_roles', 'rbac_audit_log'];
    $allTablesExist = true;
    
    foreach ($tables as $table) {
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
        if ($result->fetch()) {
            echo "  ✓ Table '$table' exists\n";
        } else {
            echo "  ✗ Table '$table' missing\n";
            $allTablesExist = false;
        }
    }
    
    if ($allTablesExist) {
        echo "\n✓ RBAC migration completed successfully!\n\n";
        
        // Show role and permission counts
        $roleCount = $db->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        $permCount = $db->query("SELECT COUNT(*) FROM permissions")->fetchColumn();
        $userRoleCount = $db->query("SELECT COUNT(*) FROM user_roles")->fetchColumn();
        
        echo "Statistics:\n";
        echo "  - Roles: $roleCount\n";
        echo "  - Permissions: $permCount\n";
        echo "  - User role assignments: $userRoleCount\n\n";
        
        // Show default roles
        echo "Default Roles:\n";
        $roles = $db->query("SELECT name, display_name, level FROM roles ORDER BY level DESC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($roles as $role) {
            echo "  - {$role['display_name']} ({$role['name']}) - Level {$role['level']}\n";
        }
        
        echo "\n===========================================\n";
        echo "Next Steps:\n";
        echo "===========================================\n";
        echo "1. Update your admin pages to use RBAC\n";
        echo "2. Include 'config/RBAC.php' in your files\n";
        echo "3. Use requirePermission() or requireRole() for access control\n";
        echo "4. Test the new permission system\n\n";
        
        echo "Example usage:\n";
        echo "  require_once 'config/RBAC.php';\n";
        echo "  requirePermission(\$db, 'articles.create');\n\n";
        
    } else {
        echo "\n✗ Migration incomplete. Some tables are missing.\n";
    }
    
    closeDBConnection($db);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
