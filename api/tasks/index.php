<?php
// ============================================================
// TaskHub API - List All Tasks
// GET /api/tasks
// ============================================================
//
// WHAT IT DOES:
// Returns all non-archived tasks for the logged-in user.
// This is the API equivalent of your dashboard-page.php query.
//
// OPTIONAL QUERY PARAMETERS (for filtering):
//   ?status=Pending          → Filter by status
//   ?category=Assignment     → Filter by category
//   ?priority=High           → Filter by priority
//
// EXAMPLE REQUEST:
//   GET /api/tasks
//   GET /api/tasks?status=Pending&priority=High
//   Headers: Authorization: Bearer eyJhbG...
//
// EXAMPLE RESPONSE (200):
// {
//     "success": true,
//     "count": 2,
//     "tasks": [
//         { "task_id": 1, "title": "Finish report", ... },
//         { "task_id": 2, "title": "Study for exam", ... }
//     ]
// }
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

// Authenticate — this stops execution if token is invalid
// and returns the user_id if valid
$userId = authenticate();

// --- Build the query with optional filters ---
// We start with the base query and conditionally add WHERE clauses.
// This approach lets us support any combination of filters.
$pdo = getDBConnection();

// Base query — same as your dashboard: non-archived tasks, ordered by due date
$sql = "SELECT * FROM tasks WHERE user_id = ? AND archived = 0";
$params = [$userId];

// Check for optional query parameters
// $_GET contains URL parameters like ?status=Pending
if (!empty($_GET['status'])) {
    $sql .= " AND status = ?";
    $params[] = $_GET['status'];
}

if (!empty($_GET['category'])) {
    $sql .= " AND category = ?";
    $params[] = $_GET['category'];
}

if (!empty($_GET['priority'])) {
    $sql .= " AND priority = ?";
    $params[] = $_GET['priority'];
}

// Order by due date (soonest first)
$sql .= " ORDER BY due_date ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($tasks),
        'tasks' => $tasks
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}