<?php
// ============================================================
// TaskHub API - Get Single Task
// GET /api/tasks/{id}
// ============================================================
//
// WHAT IT DOES:
// Returns details of a specific task by its ID.
// Similar to your get_task.php, but returns JSON directly.
//
// SECURITY:
// We verify the task belongs to the logged-in user.
// User A cannot view User B's tasks — even if they guess the ID.
//
// EXAMPLE REQUEST:
//   GET /api/tasks/5
//   Headers: Authorization: Bearer eyJhbG...
//
// EXAMPLE RESPONSE (200):
// {
//     "success": true,
//     "task": { "task_id": 5, "title": "Finish report", ... }
// }
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$userId = authenticate();

// $id comes from the router (index.php) — it's the number 
// extracted from the URL path /api/tasks/{id}
// We cast it to int to prevent SQL injection through the URL
$taskId = (int) $id;

if ($taskId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid task ID'
    ]);
    exit();
}

$pdo = getDBConnection();

try {
    // Notice: WHERE includes user_id to ensure ownership
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE task_id = ? AND user_id = ?");
    $stmt->execute([$taskId, $userId]);
    $task = $stmt->fetch();

    if (!$task) {
        http_response_code(404); // 404 = Not Found
        echo json_encode([
            'success' => false,
            'message' => 'Task not found'
        ]);
        exit();
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'task' => $task
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}