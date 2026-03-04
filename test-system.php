<?php
/**
 * System Testing Page
 * Tests all components of the Runyakitara Hub
 */

require_once 'config/database.php';

$results = [];
$allPassed = true;

// Test 1: Database Connection
try {
    $db = getDBConnection();
    $results[] = ['test' => 'Database Connection', 'status' => 'PASS', 'message' => 'Successfully connected to SQLite database'];
} catch (Exception $e) {
    $results[] = ['test' => 'Database Connection', 'status' => 'FAIL', 'message' => $e->getMessage()];
    $allPassed = false;
}

// Test 2: Check Tables
$tables = ['users', 'lessons', 'dictionary', 'proverbs', 'articles', 'translations', 'grammar_topics', 'media', 'contact_messages'];
foreach ($tables as $table) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $results[] = ['test' => "Table: $table", 'status' => 'PASS', 'message' => "$count records found"];
    } catch (Exception $e) {
        $results[] = ['test' => "Table: $table", 'status' => 'FAIL', 'message' => $e->getMessage()];
        $allPassed = false;
    }
}

// Test 3: Admin User
try {
    $stmt = $db->query("SELECT * FROM users WHERE role = 'admin'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        $results[] = ['test' => 'Admin User', 'status' => 'PASS', 'message' => 'Admin user exists: ' . $admin['username']];
    } else {
        $results[] = ['test' => 'Admin User', 'status' => 'FAIL', 'message' => 'No admin user found'];
        $allPassed = false;
    }
} catch (Exception $e) {
    $results[] = ['test' => 'Admin User', 'status' => 'FAIL', 'message' => $e->getMessage()];
    $allPassed = false;
}

// Test 4: API Endpoints
$apiEndpoints = [
    'api/lessons.php',
    'api/dictionary.php',
    'api/proverbs.php',
    'api/articles.php',
    'api/translations.php',
    'api/grammar.php',
    'api/media.php'
];

foreach ($apiEndpoints as $endpoint) {
    if (file_exists($endpoint)) {
        $results[] = ['test' => "API: $endpoint", 'status' => 'PASS', 'message' => 'File exists'];
    } else {
        $results[] = ['test' => "API: $endpoint", 'status' => 'FAIL', 'message' => 'File not found'];
        $allPassed = false;
    }
}

// Test 5: Frontend Pages
$pages = [
    'index.html',
    'lessons.html',
    'dictionary.html',
    'proverbs.html',
    'grammar.html',
    'news.html',
    'media.html',
    'translations.html',
    'contact.html'
];

foreach ($pages as $page) {
    if (file_exists($page)) {
        $results[] = ['test' => "Page: $page", 'status' => 'PASS', 'message' => 'File exists'];
    } else {
        $results[] = ['test' => "Page: $page", 'status' => 'FAIL', 'message' => 'File not found'];
        $allPassed = false;
    }
}

// Test 6: Admin Pages
$adminPages = [
    'admin/login.php',
    'admin/dashboard.php',
    'admin/dictionary.php',
    'admin/messages.php'
];

foreach ($adminPages as $page) {
    if (file_exists($page)) {
        $results[] = ['test' => "Admin: $page", 'status' => 'PASS', 'message' => 'File exists'];
    } else {
        $results[] = ['test' => "Admin: $page", 'status' => 'FAIL', 'message' => 'File not found'];
        $allPassed = false;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Test - Runyakitara Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .overall-status {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
        }
        
        .overall-status.pass {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        
        .overall-status.fail {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }
        
        .test-grid {
            display: grid;
            gap: 15px;
        }
        
        .test-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-radius: 10px;
            background: #f8f9fa;
            border-left: 4px solid #ddd;
            transition: all 0.3s;
        }
        
        .test-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .test-item.pass {
            border-left-color: #28a745;
            background: #f1f9f4;
        }
        
        .test-item.fail {
            border-left-color: #dc3545;
            background: #fef5f5;
        }
        
        .test-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            min-width: 30px;
        }
        
        .test-icon.pass {
            color: #28a745;
        }
        
        .test-icon.fail {
            color: #dc3545;
        }
        
        .test-content {
            flex: 1;
        }
        
        .test-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .test-message {
            color: #666;
            font-size: 0.9rem;
        }
        
        .test-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        
        .test-status.pass {
            background: #28a745;
            color: white;
        }
        
        .test-status.fail {
            background: #dc3545;
            color: white;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            color: white;
        }
        
        .stat-card.total {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .stat-card.passed {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .stat-card.failed {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #20c997;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <i class="bi bi-clipboard-check"></i>
            System Test Results
        </h1>
        <p class="subtitle">Comprehensive testing of all Runyakitara Hub components</p>
        
        <div class="overall-status <?php echo $allPassed ? 'pass' : 'fail'; ?>">
            <?php if ($allPassed): ?>
                <i class="bi bi-check-circle-fill"></i> All Tests Passed! System is Ready
            <?php else: ?>
                <i class="bi bi-x-circle-fill"></i> Some Tests Failed - Please Review
            <?php endif; ?>
        </div>
        
        <div class="stats">
            <div class="stat-card total">
                <div class="stat-number"><?php echo count($results); ?></div>
                <div class="stat-label">Total Tests</div>
            </div>
            <div class="stat-card passed">
                <div class="stat-number">
                    <?php echo count(array_filter($results, function($r) { return $r['status'] === 'PASS'; })); ?>
                </div>
                <div class="stat-label">Passed</div>
            </div>
            <div class="stat-card failed">
                <div class="stat-number">
                    <?php echo count(array_filter($results, function($r) { return $r['status'] === 'FAIL'; })); ?>
                </div>
                <div class="stat-label">Failed</div>
            </div>
        </div>
        
        <div class="test-grid">
            <?php foreach ($results as $result): ?>
                <div class="test-item <?php echo strtolower($result['status']); ?>">
                    <div class="test-icon <?php echo strtolower($result['status']); ?>">
                        <?php if ($result['status'] === 'PASS'): ?>
                            <i class="bi bi-check-circle-fill"></i>
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill"></i>
                        <?php endif; ?>
                    </div>
                    <div class="test-content">
                        <div class="test-name"><?php echo htmlspecialchars($result['test']); ?></div>
                        <div class="test-message"><?php echo htmlspecialchars($result['message']); ?></div>
                    </div>
                    <div class="test-status <?php echo strtolower($result['status']); ?>">
                        <?php echo $result['status']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="actions">
            <a href="index.html" class="btn btn-primary">
                <i class="bi bi-house-fill"></i>
                Go to Homepage
            </a>
            <a href="init-database.php" class="btn btn-success">
                <i class="bi bi-database-fill-add"></i>
                Initialize Database
            </a>
            <a href="admin/login.php" class="btn btn-secondary">
                <i class="bi bi-shield-lock-fill"></i>
                Admin Login
            </a>
            <a href="test-system.php" class="btn btn-secondary">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh Tests
            </a>
        </div>
    </div>
</body>
</html>
