<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Runyakitara Hub API v1 Documentation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 20px;
            opacity: 0.9;
        }
        
        .version-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 20px;
            margin-top: 20px;
            font-weight: 600;
        }
        
        .content {
            padding: 40px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        .endpoint {
            background: #f8fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .method {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
        }
        
        .method-get {
            background: #10b981;
            color: white;
        }
        
        .method-post {
            background: #3b82f6;
            color: white;
        }
        
        .endpoint-url {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .endpoint-description {
            color: #64748b;
            margin-bottom: 15px;
        }
        
        .params {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
        
        .params h4 {
            color: #475569;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .param {
            display: flex;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .param:last-child {
            border-bottom: none;
        }
        
        .param-name {
            font-family: 'Courier New', monospace;
            color: #667eea;
            font-weight: 600;
            min-width: 120px;
        }
        
        .param-desc {
            color: #64748b;
            flex: 1;
        }
        
        .example {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin-top: 15px;
        }
        
        .example-title {
            color: #94a3b8;
            margin-bottom: 10px;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .base-url {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .base-url strong {
            color: #92400e;
        }
        
        .base-url code {
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌍 Runyakitara Hub API</h1>
            <p>RESTful API for Runyakitara language resources</p>
            <span class="version-badge">Version 1.0</span>
        </div>
        
        <div class="content">
            <div class="base-url">
                <strong>Base URL:</strong> <code>http://localhost/RUNYAKITARA%20HUB/api/v1/</code>
            </div>
            
            <div class="section">
                <h2>📚 Dictionary Endpoints</h2>
                
                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-url">/api/v1/dictionary</span>
                    </div>
                    <div class="endpoint-description">Get all dictionary words with pagination and filtering</div>
                    <div class="params">
                        <h4>Query Parameters:</h4>
                        <div class="param">
                            <span class="param-name">page</span>
                            <span class="param-desc">Page number (default: 1)</span>
                        </div>
                        <div class="param">
                            <span class="param-name">limit</span>
                            <span class="param-desc">Items per page (default: 20, max: 100)</span>
                        </div>
                        <div class="param">
                            <span class="param-name">search</span>
                            <span class="param-desc">Search in word or translation</span>
                        </div>
                        <div class="param">
                            <span class="param-name">category</span>
                            <span class="param-desc">Filter by category</span>
                        </div>
                    </div>
                    <div class="example">
                        <div class="example-title">Example Request:</div>
                        GET /api/v1/dictionary?page=1&limit=10&category=greetings
                    </div>
                </div>
                
                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-url">/api/v1/dictionary/{id}</span>
                    </div>
                    <div class="endpoint-description">Get a specific dictionary word by ID</div>
                    <div class="example">
                        <div class="example-title">Example Request:</div>
                        GET /api/v1/dictionary/1
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>📖 Lessons Endpoints</h2>
                
                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-url">/api/v1/lessons</span>
                    </div>
                    <div class="endpoint-description">Get all lessons with pagination and filtering</div>
                    <div class="params">
                        <h4>Query Parameters:</h4>
                        <div class="param">
                            <span class="param-name">page</span>
                            <span class="param-desc">Page number</span>
                        </div>
                        <div class="param">
                            <span class="param-name">limit</span>
                            <span class="param-desc">Items per page</span>
                        </div>
                        <div class="param">
                            <span class="param-name">level</span>
                            <span class="param-desc">Filter by level (beginner, intermediate, advanced)</span>
                        </div>
                    </div>
                </div>
                
                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-url">/api/v1/lessons/{id}</span>
                    </div>
                    <div class="endpoint-description">Get a specific lesson by ID</div>
                </div>
            </div>
            
            <div class="section">
                <h2>✍️ Grammar Endpoints</h2>
                
                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-url">/api/v1/grammar</span>
                    </div>
                    <div class="endpoint-description">Get all grammar topics</div>
                    <div class="params">
                        <h4>Query Parameters:</h4>
                        <div class="param">
                            <span class="param-name">difficulty</span>
                            <span class="param-desc">Filter by difficulty (easy, medium, hard)</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>💬 Proverbs Endpoints</h2>
                
                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-url">/api/v1/proverbs</span>
                    </div>
                    <div class="endpoint-description">Get all proverbs</div>
                    <div class="params">
                        <h4>Query Parameters:</h4>
                        <div class="param">
                            <span class="param-name">search</span>
                            <span class="param-desc">Search in proverb, translation, or meaning</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>📰 Articles Endpoints</h2>
                
                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-url">/api/v1/articles</span>
                    </div>
                    <div class="endpoint-description">Get all articles</div>
                    <div class="params">
                        <h4>Query Parameters:</h4>
                        <div class="param">
                            <span class="param-name">category</span>
                            <span class="param-desc">Filter by category</span>
                        </div>
                        <div class="param">
                            <span class="param-name">author</span>
                            <span class="param-desc">Filter by author</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>📧 Contact Endpoint</h2>
                
                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method method-post">POST</span>
                        <span class="endpoint-url">/api/v1/contact</span>
                    </div>
                    <div class="endpoint-description">Submit a contact form</div>
                    <div class="params">
                        <h4>Request Body (JSON):</h4>
                        <div class="param">
                            <span class="param-name">name</span>
                            <span class="param-desc">Sender's name (required)</span>
                        </div>
                        <div class="param">
                            <span class="param-name">email</span>
                            <span class="param-desc">Sender's email (required)</span>
                        </div>
                        <div class="param">
                            <span class="param-name">subject</span>
                            <span class="param-desc">Message subject (required)</span>
                        </div>
                        <div class="param">
                            <span class="param-name">message</span>
                            <span class="param-desc">Message content (required)</span>
                        </div>
                    </div>
                    <div class="example">
                        <div class="example-title">Example Request:</div>
{
  "name": "John Doe",
  "email": "john@example.com",
  "subject": "Question about lessons",
  "message": "I would like to know more..."
}
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>📋 Response Format</h2>
                <div class="example">
                    <div class="example-title">Success Response:</div>
{
  "success": true,
  "version": "v1",
  "data": { ... },
  "timestamp": "2025-02-17T10:30:00+00:00"
}
                </div>
                
                <div class="example">
                    <div class="example-title">Error Response:</div>
{
  "success": false,
  "version": "v1",
  "error": "Error message",
  "timestamp": "2025-02-17T10:30:00+00:00"
}
                </div>
                
                <div class="example">
                    <div class="example-title">Paginated Response:</div>
{
  "success": true,
  "version": "v1",
  "data": {
    "items": [ ... ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 150,
      "pages": 8
    }
  },
  "timestamp": "2025-02-17T10:30:00+00:00"
}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
