<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Runyakitara Hub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .access-denied-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .icon-wrapper {
            width: 120px;
            height: 120px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        
        .icon-wrapper i {
            font-size: 60px;
            color: #dc2626;
        }
        
        h1 {
            font-size: 32px;
            color: #1f2937;
            margin-bottom: 15px;
        }
        
        .error-code {
            font-size: 18px;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        p {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
        }
        
        .info-box h3 {
            color: #0369a1;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-box ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info-box li {
            color: #075985;
            padding: 5px 0;
            font-size: 14px;
        }
        
        .info-box li:before {
            content: "•";
            margin-right: 10px;
            color: #0284c7;
        }
    </style>
</head>
<body>
    <div class="access-denied-container">
        <div class="icon-wrapper">
            <i class="bi bi-shield-x"></i>
        </div>
        
        <h1>Access Denied</h1>
        <div class="error-code">Error 403 - Forbidden</div>
        
        <p>
            You don't have permission to access this resource. 
            This page requires specific privileges that your account doesn't currently have.
        </p>
        
        <div class="actions">
            <a href="dashboard-new.php" class="btn btn-primary">
                <i class="bi bi-house-door"></i>
                Go to Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Go Back
            </a>
        </div>
        
        <div class="info-box">
            <h3>
                <i class="bi bi-info-circle"></i>
                Need Access?
            </h3>
            <ul>
                <li>Contact your system administrator to request permissions</li>
                <li>Verify you're logged in with the correct account</li>
                <li>Check if your role has the necessary privileges</li>
            </ul>
        </div>
    </div>
</body>
</html>
