<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word_runyakitara = $_POST['word_runyakitara'];
    $word_english = $_POST['word_english'];
    $category = $_POST['category'];
    $pronunciation = $_POST['pronunciation'];
    
    $stmt = $db->prepare("INSERT INTO dictionary (word_runyakitara, word_english, category, pronunciation) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$word_runyakitara, $word_english, $category, $pronunciation])) {
        $success = "Word added successfully!";
    }
}

// Get all dictionary entries
$result = $db->query("SELECT * FROM dictionary ORDER BY word_runyakitara ASC");
$entries = $result->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dictionary Management - Runyakitara Hub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="logo">Runyakitara Hub</div>
            <nav class="admin-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="lessons.php">Lessons</a>
                <a href="dictionary.php" class="active">Dictionary</a>
                <a href="proverbs.php">Proverbs</a>
                <a href="articles.php">Articles</a>
                <a href="messages.php">Messages</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1>Dictionary Management</h1>
            </header>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="admin-form">
                <h2>Add New Word</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="word_runyakitara">Runyakitara Word</label>
                        <input type="text" id="word_runyakitara" name="word_runyakitara" required>
                    </div>
                    <div class="form-group">
                        <label for="word_english">English Translation</label>
                        <input type="text" id="word_english" name="word_english" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="Animals">Animals</option>
                            <option value="Body Parts">Body Parts</option>
                            <option value="Family">Family</option>
                            <option value="Food">Food</option>
                            <option value="Nature">Nature</option>
                            <option value="Verbs">Verbs</option>
                            <option value="Greetings">Greetings</option>
                            <option value="Numbers">Numbers</option>
                            <option value="Colors">Colors</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pronunciation">Pronunciation</label>
                        <input type="text" id="pronunciation" name="pronunciation">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Word</button>
                </form>
            </div>
            
            <div class="data-table" style="margin-top: 2rem;">
                <table>
                    <thead>
                        <tr>
                            <th>Runyakitara</th>
                            <th>English</th>
                            <th>Category</th>
                            <th>Pronunciation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['word_runyakitara']); ?></td>
                            <td><?php echo htmlspecialchars($row['word_english']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['pronunciation']); ?></td>
                            <td class="action-links">
                                <a href="?delete=<?php echo $row['id']; ?>">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
<?php closeDBConnection($db); ?>
