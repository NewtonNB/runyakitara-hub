<?php
/**
 * Comprehensive Database Schema Fix
 * This script checks and updates all tables to match the expected schema
 */

require_once 'config/database.php';

try {
    $db = getDBConnection();
    
    echo "==============================================\n";
    echo "  DATABASE SCHEMA FIX UTILITY\n";
    echo "==============================================\n\n";
    
    $updates = 0;
    
    // 1. Fix proverbs table
    echo "1. Checking proverbs table...\n";
    $stmt = $db->query("PRAGMA table_info(proverbs)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    if (in_array('proverb_text', $columnNames) && !in_array('proverb', $columnNames)) {
        echo "   Fixing proverbs table schema...\n";
        $db->exec("BEGIN TRANSACTION");
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
        $db->exec("INSERT INTO proverbs_new (id, proverb, translation, meaning, created_at)
                   SELECT id, proverb_text, translation, meaning, created_at FROM proverbs");
        $db->exec("DROP TABLE proverbs");
        $db->exec("ALTER TABLE proverbs_new RENAME TO proverbs");
        $db->exec("COMMIT");
        echo "   ✓ Proverbs table fixed!\n";
        $updates++;
    } elseif (!in_array('usage', $columnNames)) {
        $db->exec("ALTER TABLE proverbs ADD COLUMN usage TEXT");
        echo "   ✓ Added 'usage' column to proverbs\n";
        $updates++;
    } else {
        echo "   ✓ Proverbs table is correct\n";
    }
    
    // 2. Fix grammar_topics table
    echo "\n2. Checking grammar_topics table...\n";
    $stmt = $db->query("PRAGMA table_info(grammar_topics)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    if (!in_array('difficulty', $columnNames)) {
        $db->exec("ALTER TABLE grammar_topics ADD COLUMN difficulty VARCHAR(20) DEFAULT 'medium'");
        echo "   ✓ Added 'difficulty' column to grammar_topics\n";
        $updates++;
    } else {
        echo "   ✓ Grammar_topics table is correct\n";
    }
    
    // 3. Check lessons table
    echo "\n3. Checking lessons table...\n";
    $stmt = $db->query("PRAGMA table_info(lessons)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    if (!in_array('level', $columnNames)) {
        $db->exec("ALTER TABLE lessons ADD COLUMN level VARCHAR(20) DEFAULT 'beginner'");
        echo "   ✓ Added 'level' column to lessons\n";
        $updates++;
    } else {
        echo "   ✓ Lessons table is correct\n";
    }
    
    if (!in_array('vocabulary', $columnNames)) {
        $db->exec("ALTER TABLE lessons ADD COLUMN vocabulary TEXT");
        echo "   ✓ Added 'vocabulary' column to lessons\n";
        $updates++;
    }
    
    // 4. Check dictionary table
    echo "\n4. Checking dictionary table...\n";
    $stmt = $db->query("PRAGMA table_info(dictionary)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    if (!in_array('pronunciation', $columnNames)) {
        $db->exec("ALTER TABLE dictionary ADD COLUMN pronunciation VARCHAR(100)");
        echo "   ✓ Added 'pronunciation' column to dictionary\n";
        $updates++;
    } else {
        echo "   ✓ Dictionary table is correct\n";
    }
    
    if (!in_array('example', $columnNames)) {
        $db->exec("ALTER TABLE dictionary ADD COLUMN example TEXT");
        echo "   ✓ Added 'example' column to dictionary\n";
        $updates++;
    }
    
    if (!in_array('category', $columnNames)) {
        $db->exec("ALTER TABLE dictionary ADD COLUMN category VARCHAR(50)");
        echo "   ✓ Added 'category' column to dictionary\n";
        $updates++;
    }
    
    // 5. Check articles table
    echo "\n5. Checking articles table...\n";
    $stmt = $db->query("PRAGMA table_info(articles)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    if (!in_array('category', $columnNames)) {
        $db->exec("ALTER TABLE articles ADD COLUMN category VARCHAR(50)");
        echo "   ✓ Added 'category' column to articles\n";
        $updates++;
    } else {
        echo "   ✓ Articles table is correct\n";
    }
    
    // 6. Check translations table
    echo "\n6. Checking translations table...\n";
    $stmt = $db->query("PRAGMA table_info(translations)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    if (!in_array('type', $columnNames)) {
        $db->exec("ALTER TABLE translations ADD COLUMN type VARCHAR(50)");
        echo "   ✓ Added 'type' column to translations\n";
        $updates++;
    } else {
        echo "   ✓ Translations table is correct\n";
    }
    
    // 7. Check media table
    echo "\n7. Checking media table...\n";
    $stmt = $db->query("PRAGMA table_info(media)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    if (!in_array('type', $columnNames)) {
        $db->exec("ALTER TABLE media ADD COLUMN type VARCHAR(20)");
        echo "   ✓ Added 'type' column to media\n";
        $updates++;
    } else {
        echo "   ✓ Media table is correct\n";
    }
    
    if (!in_array('description', $columnNames)) {
        $db->exec("ALTER TABLE media ADD COLUMN description TEXT");
        echo "   ✓ Added 'description' column to media\n";
        $updates++;
    }
    
    // Summary
    echo "\n==============================================\n";
    if ($updates > 0) {
        echo "  ✓ Database updated successfully!\n";
        echo "  Total changes: $updates\n";
    } else {
        echo "  ✓ All tables are already up to date!\n";
    }
    echo "==============================================\n";
    
    closeDBConnection($db);
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    if (isset($db)) {
        try {
            $db->exec("ROLLBACK");
        } catch (Exception $rollbackError) {
            // Ignore rollback errors
        }
    }
    exit(1);
}
