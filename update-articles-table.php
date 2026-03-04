<?php
/**
 * Update Articles Table - Add Category Column
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Update Articles Table</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 { color: #667eea; }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
        }
    </style>
</head>
<body>
<div class='container'>";

try {
    $db = getDBConnection();
    
    echo "<h1>🔧 Updating Articles Table</h1>";
    
    // Check if category column exists
    $result = $db->query("PRAGMA table_info(articles)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $hasCategory = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'category') {
            $hasCategory = true;
            break;
        }
    }
    
    if (!$hasCategory) {
        // Add category column
        $db->exec("ALTER TABLE articles ADD COLUMN category TEXT DEFAULT 'news'");
        echo "<div class='success'>✅ Added 'category' column to articles table</div>";
        
        // Update existing articles with default categories
        $db->exec("UPDATE articles SET category = 'culture' WHERE title LIKE '%culture%' OR title LIKE '%tradition%'");
        $db->exec("UPDATE articles SET category = 'language' WHERE title LIKE '%language%' OR title LIKE '%preserv%'");
        $db->exec("UPDATE articles SET category = 'history' WHERE title LIKE '%history%' OR title LIKE '%famous%'");
        
        echo "<div class='success'>✅ Updated existing articles with appropriate categories</div>";
    } else {
        echo "<div class='success'>✅ Category column already exists</div>";
    }
    
    echo "<h2>✨ Update Complete!</h2>";
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='admin/articles-manage.php' class='btn'>📰 Manage Articles</a>";
    echo "<a href='admin/dashboard-new.php' class='btn'>📊 Dashboard</a>";
    echo "</div>";
    
    closeDBConnection($db);
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>
