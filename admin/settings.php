<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

$message = '';
$messageType = '';

// Load current settings from DB (key-value table), fallback to defaults
function getSetting($db, $key, $default = '') {
    try {
        $stmt = $db->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['value'] : $default;
    } catch (Exception $e) { return $default; }
}

// Ensure settings table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT,
        updated_at TEXT DEFAULT (datetime('now'))
    )");
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'general') {
        $fields = ['site_name', 'site_tagline', 'contact_email', 'items_per_page'];
        foreach ($fields as $field) {
            $val = trim($_POST[$field] ?? '');
            $stmt = $db->prepare("INSERT INTO settings (key, value, updated_at) VALUES (?, ?, datetime('now'))
                ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at");
            $stmt->execute([$field, $val]);
        }
        $message = 'General settings saved.';
        $messageType = 'success';

    } elseif ($action === 'password') {
        $current  = $_POST['current_password'] ?? '';
        $new      = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current, $user['password'])) {
            $message = 'Current password is incorrect.';
            $messageType = 'error';
        } elseif ($new !== $confirm) {
            $message = 'New passwords do not match.';
            $messageType = 'error';
        } elseif (strlen($new) < 6) {
            $message = 'Password must be at least 6 characters.';
            $messageType = 'error';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['user_id']]);
            $message = 'Password updated successfully.';
            $messageType = 'success';
        }
    }
}

$siteName     = getSetting($db, 'site_name', 'Runyakitara Hub');
$siteTagline  = getSetting($db, 'site_tagline', 'Learn the Runyakitara Language');
$contactEmail = getSetting($db, 'contact_email', '');
$itemsPerPage = getSetting($db, 'items_per_page', '20');

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Runyakitara Hub Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/forms.css">
</head>
<body class="admin-body">
<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <div class="admin-content">
        <?php include 'includes/header.php'; ?>

        <main class="admin-main">

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- General Settings -->
            <div class="form-section">
                <h2><i class="bi bi-sliders"></i> General Settings</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="general">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">Site Name</label>
                            <input type="text" name="site_name" value="<?php echo htmlspecialchars($siteName); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Site Tagline</label>
                            <input type="text" name="site_tagline" value="<?php echo htmlspecialchars($siteTagline); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="contact_email" value="<?php echo htmlspecialchars($contactEmail); ?>" placeholder="admin@example.com">
                        </div>
                        <div class="form-group">
                            <label>Items Per Page</label>
                            <select name="items_per_page">
                                <?php foreach ([10, 20, 50, 100] as $n): ?>
                                    <option value="<?php echo $n; ?>" <?php echo $itemsPerPage == $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Settings</button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="form-section">
                <h2><i class="bi bi-lock"></i> Change Password</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="password">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">Current Password</label>
                            <input type="password" name="current_password" required autocomplete="current-password">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">New Password</label>
                            <input type="password" name="new_password" required minlength="6" autocomplete="new-password">
                            <span class="field-hint">Minimum 6 characters</span>
                        </div>
                        <div class="form-group">
                            <label class="required">Confirm New Password</label>
                            <input type="password" name="confirm_password" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-shield-lock"></i> Update Password</button>
                    </div>
                </form>
            </div>

            <!-- Account Info -->
            <div class="form-section">
                <h2><i class="bi bi-person-circle"></i> Account Info</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" value="<?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?>" disabled>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>
</body>
</html>
