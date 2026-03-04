<?php
/**
 * Update Grammar Topics Table Schema
 * This script adds the missing 'difficulty' column to the grammar_topics table
 */

require_once 'config/database.php';

try {
    $db = getDBConnection();
    
    echo "Starting grammar_topics table update...\n\n";
    
    // Check current columns
    $stmt = $db->query("PRAGMA table_info(grammar_topics)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasDifficulty = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'difficulty') {
            $hasDifficulty = true;
        }
    }
    
    echo "Current columns:\n";
    foreach ($columns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    echo "\n";
    
    // Add difficulty column if it doesn't exist
    if (!$hasDifficulty) {
        echo "Adding 'difficulty' column...\n";
        $db->exec("ALTER TABLE grammar_topics ADD COLUMN difficulty VARCHAR(20) DEFAULT 'medium'");
        echo "✓ Column added successfully!\n\n";
    } else {
        echo "✓ Table schema is already correct!\n\n";
    }
    
    // Verify final schema
    $stmt = $db->query("PRAGMA table_info(grammar_topics)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Final schema:\n";
    foreach ($columns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    
    echo "\n✓ Grammar topics table update completed successfully!\n";
    
    closeDBConnection($db);
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
