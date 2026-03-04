<?php
/**
 * API v1 Router
 * Routes requests to appropriate v1 endpoints
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get the requested resource from the URL
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/api/v1/';

// Extract resource name
if (strpos($request_uri, $base_path) !== false) {
    $resource = str_replace($base_path, '', $request_uri);
    $resource = strtok($resource, '?'); // Remove query string
    $resource = trim($resource, '/');
    
    // Map resource to file
    $resource_file = __DIR__ . '/' . $resource . '.php';
    
    if (file_exists($resource_file)) {
        require_once $resource_file;
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Resource not found',
            'version' => 'v1'
        ]);
    }
} else {
    // API documentation endpoint
    echo json_encode([
        'success' => true,
        'version' => 'v1',
        'message' => 'Runyakitara Hub API v1',
        'endpoints' => [
            'GET /api/v1/dictionary' => 'Get all dictionary words',
            'GET /api/v1/dictionary/{id}' => 'Get specific word',
            'GET /api/v1/lessons' => 'Get all lessons',
            'GET /api/v1/lessons/{id}' => 'Get specific lesson',
            'GET /api/v1/grammar' => 'Get all grammar topics',
            'GET /api/v1/grammar/{id}' => 'Get specific grammar topic',
            'GET /api/v1/proverbs' => 'Get all proverbs',
            'GET /api/v1/proverbs/{id}' => 'Get specific proverb',
            'GET /api/v1/articles' => 'Get all articles',
            'GET /api/v1/articles/{id}' => 'Get specific article',
            'GET /api/v1/translations' => 'Get all translations',
            'GET /api/v1/translations/{id}' => 'Get specific translation',
            'GET /api/v1/media' => 'Get all media',
            'GET /api/v1/media/{id}' => 'Get specific media',
            'POST /api/v1/contact' => 'Submit contact form'
        ],
        'documentation' => '/api/v1/docs'
    ]);
}
