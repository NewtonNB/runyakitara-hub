<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Legacy support
            
            // Load RBAC roles and permissions (if tables exist)
            try {
                require_once '../config/RBAC.php';
                $rbac = new RBAC($db, $user['id']);
                $_SESSION['user_roles'] = $rbac->getUserRoles();
                $_SESSION['user_permissions'] = $rbac->getUserPermissions();
            } catch (Exception $e) {
                // RBAC tables don't exist yet - use legacy role
                $_SESSION['user_roles'] = [];
                $_SESSION['user_permissions'] = [];
            }
            
            header('Location: dashboard-new.php');
            exit;
        }
        
        $error = "Invalid username or password";
        closeDBConnection($db);
    } catch (Exception $e) {
        $error = "Login error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Runyakitara Hub</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
            z-index: 0;
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 1200px;
            margin: 40px auto;
            position: relative;
            z-index: 1;
        }
        
        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }
        
        .login-left {
            color: white;
            padding: 40px;
        }
        
        .brand-section {
            margin-bottom: 50px;
        }
        
        .brand-logo {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .brand-section h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        
        .brand-section p {
            font-size: 18px;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .features-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
            margin-top: 40px;
        }
        
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(10px);
        }
        
        .feature-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        
        .feature-content h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .feature-content p {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.5;
        }
        
        .login-box {
            background: white;
            border-radius: 24px;
            padding: 50px 45px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .login-header p {
            font-size: 15px;
            color: #64748b;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .error-message i {
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 14px 45px 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            pointer-events: none;
        }
        
        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 5px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .toggle-password:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #4a5568;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-footer {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        
        .back-home:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        
        .login-help {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }
        
        .login-help a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        @media (max-width: 968px) {
            .login-container {
                grid-template-columns: 1fr;
            }
            
            .login-left {
                display: none;
            }
            
            .login-box {
                padding: 40px 30px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .login-wrapper {
                margin: 20px auto;
            }
            
            .login-box {
                padding: 30px 20px;
            }
            
            .login-header h2 {
                font-size: 24px;
            }
            
            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-left">
                <div class="brand-section">
                    <div class="brand-logo">
                        <i class="bi bi-translate"></i>
                    </div>
                    <h1>Runyakitara Hub</h1>
                    <p>Admin Portal - Manage Your Content</p>
                </div>
                
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Secure Access</h3>
                            <p>Your credentials are encrypted and protected with industry-standard security</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Fast & Efficient</h3>
                            <p>Manage lessons, dictionary, and content with our intuitive dashboard</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-globe"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Global Impact</h3>
                            <p>Help preserve Runyakitara languages for future generations worldwide</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="login-box">
                <div class="login-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to access your admin dashboard</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
                            <i class="bi bi-person-fill input-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="login-btn">
                        <span>Sign In</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
                
                <div class="login-footer">
                    <a href="../index.html" class="back-home">
                        <i class="bi bi-house-door"></i>
                        <span>Back to Website</span>
                    </a>
                </div>
                
                <div class="login-help">
                    Need help? <a href="../contact.html">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
        
        // Add loading state to submit button
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const btn = this.querySelector('.login-btn');
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> <span>Signing in...</span>';
            btn.disabled = true;
        });
        
        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>
