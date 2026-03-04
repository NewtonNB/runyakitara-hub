<?php
/**
 * Simple API Test Script
 * Run this from command line: php test-api-simple.php
 */

echo "==============================================\n";
echo "  API v1 Test Suite\n";
echo "==============================================\n\n";

// Test 1: Include BaseAPI
echo "Test 1: Loading BaseAPI class...\n";
try {
    require_once 'api/v1/BaseAPI.php';
    echo "✓ BaseAPI loaded successfully\n\n";
} catch (Exception $e) {
    echo "✗ Failed to load BaseAPI: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Database connection
echo "Test 2: Testing database connection...\n";
try {
    require_once 'config/database.php';
    $db = getDBConnection();
    echo "✓ Database connected successfully\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Query dictionary table
echo "Test 3: Querying dictionary table...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM dictionary");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Dictionary table has {$result['count']} words\n\n";
} catch (Exception $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n\n";
}

// Test 4: Query lessons table
echo "Test 4: Querying lessons table...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM lessons");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Lessons table has {$result['count']} lessons\n\n";
} catch (Exception $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n\n";
}

// Test 5: Query grammar_topics table
echo "Test 5: Querying grammar_topics table...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM grammar_topics");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Grammar topics table has {$result['count']} topics\n\n";
} catch (Exception $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n\n";
}

// Test 6: Query proverbs table
echo "Test 6: Querying proverbs table...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM proverbs");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Proverbs table has {$result['count']} proverbs\n\n";
} catch (Exception $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n\n";
}

// Test 7: Query articles table
echo "Test 7: Querying articles table...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM articles");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Articles table has {$result['count']} articles\n\n";
} catch (Exception $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n\n";
}

// Test 8: Test API file exists
echo "Test 8: Checking API endpoint files...\n";
$endpoints = ['dictionary', 'lessons', 'grammar', 'proverbs', 'articles', 'translations', 'media', 'contact'];
$allExist = true;
foreach ($endpoints as $endpoint) {
    $file = "api/v1/{$endpoint}.php";
    if (file_exists($file)) {
        echo "  ✓ {$endpoint}.php exists\n";
    } else {
        echo "  ✗ {$endpoint}.php missing\n";
        $allExist = false;
    }
}
if ($allExist) {
    echo "✓ All API endpoint files exist\n\n";
} else {
    echo "✗ Some API files are missing\n\n";
}

closeDBConnection($db);

echo "==============================================\n";
echo "  All tests completed!\n";
echo "==============================================\n\n";

echo "To test the API via HTTP:\n";
echo "1. Make sure Apache is running (start.bat)\n";
echo "2. Open: http://localhost/RUNYAKITARA%%20HUB/test-api.html\n";
echo "3. Or visit: http://localhost/RUNYAKITARA%%20HUB/api/v1/docs.php\n\n";
