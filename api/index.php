<?php
// ============================================================
// TaskHub API - Main Router (Front Controller)
// ============================================================
//
// WHAT IS A FRONT CONTROLLER?
// Every API request goes through this single file first.
// It reads the URL and HTTP method, then decides which 
// handler file to call. Think of it as a receptionist that 
// directs visitors to the right office.
//
// EXAMPLE FLOW:
//   Request: GET /api/tasks/5
//   1. .htaccess sends it here
//   2. This file reads: path = "tasks/5", method = "GET"
//   3. It matches "tasks/{id}" pattern
//   4. It calls tasks/show.php with $id = 5
// ============================================================

// --- CORS HEADERS ---
// CORS = Cross-Origin Resource Sharing
// These headers allow requests from other domains/tools (like Postman 
// or a frontend app running on a different port).
// Without these, browsers would block requests to your API.
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// All API responses are JSON, so set this header once here
// instead of repeating it in every endpoint file
header("Content-Type: application/json; charset=UTF-8");

// --- HANDLE PREFLIGHT REQUESTS ---
// Browsers send an OPTIONS request before the actual request to check 
// if the server allows it. We just respond with 200 OK and exit.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- PARSE THE REQUEST ---
// Get the HTTP method (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// Get the URL path after /api/
// Example: if URL is /project/ToDoListApp/api/tasks/5
// We want just: "tasks/5"
$requestUri = $_SERVER['REQUEST_URI'];

// Find where "/api/" appears in the URL, then grab everything after it
$apiPosition = strpos($requestUri, '/api/');
if ($apiPosition !== false) {
    $path = substr($requestUri, $apiPosition + 5); // +5 to skip "/api/"
} else {
    $path = '';
}

// Remove query string if present (everything after ?)
// Example: "tasks?status=pending" becomes "tasks"
$path = strtok($path, '?');

// Remove trailing slash if present
// Example: "tasks/" becomes "tasks"
$path = rtrim($path, '/');

// Split the path into parts
// Example: "tasks/5" becomes ["tasks", "5"]
// Example: "auth/login" becomes ["auth", "login"]
$parts = explode('/', $path);

// The first part is the resource (tasks, auth, notifications)
$resource = $parts[0] ?? '';

// The second part could be an ID or sub-resource
$id = $parts[1] ?? null;


// --- ROUTE THE REQUEST ---
// This is where we match the URL + method to the right handler file
// It works like a map: URL pattern → file to execute
switch ($resource) {

    // =====================
    // AUTH ROUTES
    // =====================
    case 'auth':
        // For auth, the second part tells us which action
        // POST /api/auth/login  → auth/login.php
        // POST /api/auth/register → auth/register.php
        if ($id === 'login' && $method === 'POST') {
            require 'auth/login.php';
        } elseif ($id === 'register' && $method === 'POST') {
            require 'auth/register.php';
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Auth endpoint not found. Available: POST /api/auth/login, POST /api/auth/register'
            ]);
        }
        break;

    // =====================
    // TASK ROUTES
    // =====================
    case 'tasks':
        if ($method === 'GET' && $id === null) {
            // GET /api/tasks → List all tasks
            require 'tasks/index.php';
        } elseif ($method === 'GET' && $id !== null) {
            // GET /api/tasks/5 → Get single task
            require 'tasks/show.php';
        } elseif ($method === 'POST' && $id === null) {
            // POST /api/tasks → Create new task
            require 'tasks/create.php';
        } elseif ($method === 'PUT' && $id !== null) {
            // PUT /api/tasks/5 → Update task
            require 'tasks/update.php';
        } elseif ($method === 'DELETE' && $id !== null) {
            // DELETE /api/tasks/5 → Delete task
            require 'tasks/delete.php';
        } else {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed for this endpoint'
            ]);
        }
        break;

    // =====================
    // NOTIFICATION ROUTES
    // =====================
    case 'notifications':
        if ($method === 'GET' && $id === null) {
            // GET /api/notifications → List all notifications
            require 'notifications/index.php';
        } elseif ($method === 'PUT' && $id !== null) {
            // PUT /api/notifications/5 → Mark as read
            require 'notifications/update.php';
        } else {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed for this endpoint'
            ]);
        }
        break;

    // =====================
    // API ROOT / UNKNOWN ROUTES
    // =====================
    case '':
        // GET /api/ → Show a welcome message with available endpoints
        echo json_encode([
            'success' => true,
            'message' => 'Welcome to TaskHub API',
            'version' => '1.0',
            'endpoints' => [
                'POST /api/auth/register'       => 'Create new account',
                'POST /api/auth/login'           => 'Login and get token',
                'GET /api/tasks'                 => 'List all tasks (auth required)',
                'GET /api/tasks/{id}'            => 'Get single task (auth required)',
                'POST /api/tasks'                => 'Create task (auth required)',
                'PUT /api/tasks/{id}'            => 'Update task (auth required)',
                'DELETE /api/tasks/{id}'         => 'Delete task (auth required)',
                'GET /api/notifications'         => 'List notifications (auth required)',
                'PUT /api/notifications/{id}'    => 'Mark as read (auth required)',
            ]
        ]);
        break;

    default:
        // Unknown resource — return 404
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => "Resource '$resource' not found"
        ]);
        break;
}
