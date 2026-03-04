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

// Check for success messages from redirects
if (isset($_GET['success'])) {
    $message = 'Media added successfully!';
    $messageType = 'success';
} elseif (isset($_GET['updated'])) {
    $message = 'Media updated successfully!';
    $messageType = 'success';
}

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/media/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        $filePath = '';
        
        // Handle file upload
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['media_file'];
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            $targetPath = $uploadDir . $fileName;
            
            // Auto-detect media type from file if not matching
            $fileType = $file['type'];
            $detectedType = '';
            
            if (strpos($fileType, 'image/') === 0) {
                $detectedType = 'image';
            } elseif (strpos($fileType, 'video/') === 0) {
                $detectedType = 'video';
            } elseif (strpos($fileType, 'audio/') === 0) {
                $detectedType = 'audio';
            }
            
            // If selected type doesn't match file, use detected type
            if ($detectedType && $type !== $detectedType) {
                $type = $detectedType;
            }
            
            // Validate file type
            $allowedTypes = [
                'audio' => ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'],
                'video' => ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/webm'],
                'image' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
            ];
            
            if (isset($allowedTypes[$type]) && in_array($file['type'], $allowedTypes[$type])) {
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $filePath = 'uploads/media/' . $fileName;
                } else {
                    $message = 'Error uploading file. Check folder permissions.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Invalid file type: ' . $file['type'] . '. Supported types: MP3, WAV, OGG, MP4, WEBM, AVI, MOV, JPG, PNG, GIF, WEBP';
                $messageType = 'error';
            }
        } else {
            $uploadError = $_FILES['media_file']['error'] ?? 'No file uploaded';
            $message = 'Please select a file to upload. Error: ' . $uploadError;
            $messageType = 'error';
        }
        
        if ($filePath) {
            try {
                $stmt = $db->prepare("INSERT INTO media (title, type, category, file_path, description, created_at) VALUES (?, ?, ?, ?, ?, datetime('now'))");
                if ($stmt->execute([$title, $type, $category, $filePath, $description])) {
                    $message = 'Media added successfully!';
                    $messageType = 'success';
                    // Redirect to clear POST data
                    header('Location: media-manage.php?success=1');
                    exit;
                } else {
                    $message = 'Error adding media to database.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        
        // Get current file path
        $stmt = $db->prepare("SELECT file_path FROM media WHERE id=?");
        $stmt->execute([$id]);
        $currentMedia = $stmt->fetch(PDO::FETCH_ASSOC);
        $filePath = $currentMedia['file_path'];
        
        // Handle new file upload if provided
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['media_file'];
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            $targetPath = $uploadDir . $fileName;
            
            $allowedTypes = [
                'audio' => ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'],
                'video' => ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/webm'],
                'image' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
            ];
            
            if (isset($allowedTypes[$type]) && in_array($file['type'], $allowedTypes[$type])) {
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Delete old file
                    if ($filePath && file_exists('../' . $filePath)) {
                        unlink('../' . $filePath);
                    }
                    $filePath = 'uploads/media/' . $fileName;
                }
            }
        }
        
        $stmt = $db->prepare("UPDATE media SET title=?, type=?, category=?, file_path=?, description=? WHERE id=?");
        if ($stmt->execute([$title, $type, $category, $filePath, $description, $id])) {
            $message = 'Media updated successfully!';
            $messageType = 'success';
            header('Location: media-manage.php?updated=1');
            exit;
        } else {
            $message = 'Error updating media.';
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        
        // Get file path before deleting
        $stmt = $db->prepare("SELECT file_path FROM media WHERE id=?");
        $stmt->execute([$id]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare("DELETE FROM media WHERE id=?");
        if ($stmt->execute([$id])) {
            // Delete file from server
            if ($media && $media['file_path'] && file_exists('../' . $media['file_path'])) {
                unlink('../' . $media['file_path']);
            }
            $message = 'Media deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting media.';
            $messageType = 'error';
        }
    }
}

$media = [];
try {
    $stmt = $db->query("SELECT * FROM media ORDER BY created_at DESC");
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $media = [];
}

$editMedia = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM media WHERE id=?");
    $stmt->execute([$editId]);
    $editMedia = $stmt->fetch(PDO::FETCH_ASSOC);
}

closeDBConnection($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Media - Runyakitara Hub Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/form-validation.css">
    <style>
        .content-table, .form-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        .media-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }
        .media-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .media-thumbnail {
            height: 180px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
        }
        .media-thumbnail.audio {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }
        .media-thumbnail.video {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }
        .media-thumbnail.image {
            background: linear-gradient(135deg, #fa709a, #fee140);
            overflow: hidden;
        }
        .media-thumbnail.image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .media-body {
            padding: 20px;
        }
        .media-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }
        .media-description {
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.5;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .media-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }
        .type-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .type-audio {
            background: rgba(240, 147, 251, 0.1);
            color: #f5576c;
        }
        .type-video {
            background: rgba(79, 172, 254, 0.1);
            color: #00f2fe;
        }
        .type-image {
            background: rgba(250, 112, 154, 0.1);
            color: #fa709a;
        }
        .form-section h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 16px;
        }
        .btn-edit {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }
        .btn-edit:hover {
            background: var(--info);
            color: white;
        }
        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        .btn-delete:hover {
            background: var(--danger);
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <?php include 'includes/header.php'; ?>
            
            <main class="admin-main">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="bi bi-check-circle"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-section">
                    <h2>
                        <i class="bi bi-<?php echo $editMedia ? 'pencil' : 'plus-circle'; ?>"></i>
                        <?php echo $editMedia ? 'Edit Media' : 'Add New Media'; ?>
                    </h2>
                    <form method="POST" enctype="multipart/form-data" data-validate="true" id="mediaForm">
                        <input type="hidden" name="action" value="<?php echo $editMedia ? 'edit' : 'add'; ?>">
                        <?php if ($editMedia): ?>
                            <input type="hidden" name="id" value="<?php echo $editMedia['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title" class="required">Media Title</label>
                                <input type="text" id="title" name="title" required minlength="3" maxlength="200"
                                       value="<?php echo $editMedia ? htmlspecialchars($editMedia['title']) : ''; ?>"
                                       placeholder="e.g., Pronunciation Guide">
                                <span class="field-hint">3-200 characters required</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="type" class="required">Media Type</label>
                                <select id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="audio" <?php echo ($editMedia && $editMedia['type'] === 'audio') ? 'selected' : ''; ?>>Audio</option>
                                    <option value="video" <?php echo ($editMedia && $editMedia['type'] === 'video') ? 'selected' : ''; ?>>Video</option>
                                    <option value="image" <?php echo ($editMedia && $editMedia['type'] === 'image') ? 'selected' : ''; ?>>Image</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" id="category" name="category" maxlength="50"
                                   value="<?php echo $editMedia ? htmlspecialchars($editMedia['category'] ?? '') : ''; ?>"
                                   placeholder="e.g., Lessons, Songs, Stories">
                        </div>
                        
                        <div class="form-group">
                            <label for="media_file" class="<?php echo !$editMedia ? 'required' : ''; ?>">
                                <?php echo $editMedia ? 'Replace Media File (Optional)' : 'Upload Media File'; ?>
                            </label>
                            <input type="file" id="media_file" name="media_file" 
                                   accept="audio/*,video/*,image/*" <?php echo !$editMedia ? 'required' : ''; ?>>
                            <span class="field-hint">
                                Supported: Audio (MP3, WAV, OGG) | Video (MP4, WEBM, AVI, MOV) | Image (JPG, PNG, GIF, WEBP)
                                <?php if ($editMedia && $editMedia['file_path']): ?>
                                <br>Current: <?php echo basename($editMedia['file_path']); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"
                                      placeholder="Describe this media..."><?php echo $editMedia ? htmlspecialchars($editMedia['description'] ?? '') : ''; ?></textarea>
                            <span class="field-hint">Optional</span>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-<?php echo $editMedia ? 'check' : 'upload'; ?>-circle"></i>
                                <?php echo $editMedia ? 'Update Media' : 'Upload Media'; ?>
                            </button>
                            <?php if ($editMedia): ?>
                                <a href="media-manage.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <div class="content-table">
                    <h2 style="margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-play-circle"></i>
                        Media Library (<?php echo count($media); ?>)
                    </h2>
                    
                    <?php if (empty($media)): ?>
                        <div style="text-align: center; padding: 60px; color: var(--text-light);">
                            <i class="bi bi-play-circle" style="font-size: 64px; opacity: 0.5; display: block; margin-bottom: 16px;"></i>
                            <h3 style="margin-bottom: 8px;">No media yet</h3>
                            <p>Add your first audio or video above!</p>
                        </div>
                    <?php else: ?>
                        <div class="media-grid">
                            <?php foreach ($media as $item): ?>
                                <div class="media-card">
                                    <div class="media-thumbnail <?php echo $item['type']; ?>">
                                        <?php if ($item['type'] === 'image'): ?>
                                            <img src="../<?php echo htmlspecialchars($item['file_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                        <?php else: ?>
                                            <i class="bi bi-<?php echo $item['type'] === 'audio' ? 'music-note-beamed' : 'play-circle'; ?>"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="media-body">
                                        <div class="media-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <?php if ($item['description']): ?>
                                            <div class="media-description"><?php echo htmlspecialchars($item['description']); ?></div>
                                        <?php endif; ?>
                                        <div class="media-footer">
                                            <span class="type-badge type-<?php echo $item['type']; ?>">
                                                <?php echo ucfirst($item['type']); ?>
                                            </span>
                                            <div class="action-buttons">
                                                <a href="../<?php echo htmlspecialchars($item['file_path']); ?>" 
                                                   class="btn-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);" 
                                                   title="View/Play" target="_blank">
                                                    <i class="bi bi-play-circle"></i>
                                                </a>
                                                <a href="?edit=<?php echo $item['id']; ?>" class="btn-icon btn-edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this media?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn-icon btn-delete" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        document.getElementById('mobileToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Handle file upload validation
        const mediaForm = document.getElementById('mediaForm');
        const fileInput = document.getElementById('media_file');
        
        if (mediaForm) {
            mediaForm.addEventListener('submit', function(e) {
                // Check if file is selected for new uploads
                const isEdit = mediaForm.querySelector('input[name="action"]').value === 'edit';
                
                if (!isEdit && fileInput.files.length === 0) {
                    e.preventDefault();
                    alert('Please select a file to upload');
                    fileInput.focus();
                    return false;
                }
                
                return true;
            });
        }
    </script>
    <script src="js/form-validation.js"></script>
</body>
</html>
