<?php
/**
 * Update Proverbs Table Schema
 * This script updates the proverbs table to match the expected column names
 */

require_once 'config/database.php';

try {
    $db = getDBConnection();
    
    echo "Starting proverbs table update...\n\n";
    
    // Check if proverb_text column exists
    $stmt = $db->query("PRAGMA table_info(proverbs)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasProverbText = false;
    $hasProverb = false;
    $hasUsage = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'proverb_text') {
            $hasProverbText = true;
        }
        if ($column['name'] === 'proverb') {
            $hasProverb = true;
        }
        if ($column['name'] === 'usage') {
            $hasUsage = true;
        }
    }
    
    echo "Current columns:\n";
    foreach ($columns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    echo "\n";
    
    // Rename proverb_text to proverb if needed
    if ($hasProverbText && !$hasProverb) {
        echo "Renaming column 'proverb_text' to 'proverb'...\n";
        
        // SQLite doesn't support RENAME COLUMN directly in older versions
        // We need to recreate the table
        $db->exec("BEGIN TRANSACTION");
        
        // Create new table with correct schema
        $db->exec("
            CREATE TABLE proverbs_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                proverb TEXT NOT NULL,
                translation TEXT NOT NULL,
                meaning TEXT NOT NULL,
                usage TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Copy data from old table
        $db->exec("
            INSERT INTO proverbs_new (id, proverb, translation, meaning, created_at)
            SELECT id, proverb_text, translation, meaning, created_at
            FROM proverbs
        ");
        
        // Drop old table
        $db->exec("DROP TABLE proverbs");
        
        // Rename new table
        $db->exec("ALTER TABLE proverbs_new RENAME TO proverbs");
        
        $db->exec("COMMIT");
        
        echo "✓ Column renamed successfully!\n\n";
    } elseif (!$hasProverb && !$hasProverbText) {
        echo "Creating proverbs table with correct schema...\n";
        $db->exec("
            CREATE TABLE IF NOT EXISTS proverbs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                proverb TEXT NOT NULL,
                translation TEXT NOT NULL,
                meaning TEXT NOT NULL,
                usage TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✓ Table created successfully!\n\n";
    } elseif ($hasProverb && !$hasUsage) {
        echo "Adding 'usage' column...\n";
        $db->exec("ALTER TABLE proverbs ADD COLUMN usage TEXT");
        echo "✓ Column added successfully!\n\n";
    } else {
        echo "✓ Table schema is already correct!\n\n";
    }
    
    // Verify final schema
    $stmt = $db->query("PRAGMA table_info(proverbs)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Final schema:\n";
    foreach ($columns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    
    echo "\n✓ Proverbs table update completed successfully!\n";
    
    closeDBConnection($db);
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    if (isset($db)) {
        $db->exec("ROLLBACK");
    }
}
