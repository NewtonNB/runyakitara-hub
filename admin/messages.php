<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

// Get all messages
$result = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $result->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Runyakitara Hub</title>
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
                <a href="dictionary.php">Dictionary</a>
                <a href="proverbs.php">Proverbs</a>
                <a href="articles.php">Articles</a>
                <a href="messages.php" class="active">Messages</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1>Contact Messages</h1>
            </header>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['message'], 0, 50)) . '...'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td><?php echo $row['status']; ?></td>
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
