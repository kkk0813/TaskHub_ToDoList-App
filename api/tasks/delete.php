<?php
// ============================================================
// TaskHub API - Delete Task
// DELETE /api/tasks/{id}
// ============================================================
//
// WHAT IT DOES:
// Permanently deletes a task. This is the API equivalent 
// of your delete_task.php.
//
// WHY DELETE METHOD?
// REST convention maps HTTP methods to actions:
//   GET = read, POST = create, PUT = update, DELETE = remove
// Using the right method makes the API predictable — any 
// developer knows DELETE /api/tasks/5 removes task 5.
//
// SECURITY:
// We verify the task belongs to the authenticated user 
// before deleting. User A cannot delete User B's tasks.
//
// EXAMPLE REQUEST:
//   DELETE /api/tasks/5
//   Headers: Authorization: Bearer eyJhbG...
//   (No body needed — the ID is in the URL)
//
// EXAMPLE RESPONSE (200):
// {
//     "success": true,
//     "message": "Task deleted successfully"
// }
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$userId = authenticate();

// Get task ID from URL (set by router)
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
    // --- Step 1: Verify task exists and belongs to user ---
    // We check ownership first instead of just running DELETE
    // so we can give a specific error message.
    // Without this check, deleting a non-existent task and 
    // deleting someone else's task would both silently succeed
    // (DELETE on 0 rows doesn't throw an error).
    $stmt = $pdo->prepare("SELECT user_id FROM tasks WHERE task_id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Task not found'
        ]);
        exit();
    }

    if ($task['user_id'] != $userId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to delete this task'
        ]);
        exit();
    }

    // --- Step 2: Delete the task ---
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE task_id = ? AND user_id = ?");
    $result = $stmt->execute([$taskId, $userId]);

    if ($result) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete task. Please try again.'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}