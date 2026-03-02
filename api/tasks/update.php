<?php
// ============================================================
// TaskHub API - Update Task
// PUT /api/tasks/{id}
// ============================================================
//
// WHAT IT DOES:
// Updates an existing task. This is the API equivalent of 
// your update_task.php. It also handles completion dates
// (setting it when marked complete, clearing it when unmarked).
//
// WHY PUT AND NOT POST?
// REST convention: POST = create new, PUT = update existing.
// PUT means "replace/update this specific resource."
//
// WHAT THE CLIENT SENDS (JSON body):
// {
//     "title": "Updated title",
//     "description": "Updated description",
//     "category": "Assignment",
//     "due_date": "2025-04-15",
//     "priority": "Medium",
//     "status": "Completed",
//     "reminder_enabled": false
// }
//
// EXAMPLE RESPONSE (200):
// {
//     "success": true,
//     "message": "Task updated successfully"
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

// --- Step 1: Read and validate input ---
$data = json_decode(file_get_contents('php://input'));

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON. Please send a valid JSON body.'
    ]);
    exit();
}

// Validate required fields
$errors = [];

if (empty($data->title)) {
    $errors[] = 'Title is required';
}

if (empty($data->due_date)) {
    $errors[] = 'Due date is required';
}

if (empty($data->category)) {
    $errors[] = 'Category is required';
}

// Validate allowed values
$allowedCategories = ['Assignment', 'Discussion', 'Club Activity', 'Examination'];
if (!empty($data->category) && !in_array($data->category, $allowedCategories)) {
    $errors[] = 'Invalid category. Allowed: ' . implode(', ', $allowedCategories);
}

$allowedPriorities = ['High', 'Medium', 'Low'];
if (!empty($data->priority) && !in_array($data->priority, $allowedPriorities)) {
    $errors[] = 'Invalid priority. Allowed: ' . implode(', ', $allowedPriorities);
}

$allowedStatuses = ['Pending', 'On-going', 'Completed'];
if (!empty($data->status) && !in_array($data->status, $allowedStatuses)) {
    $errors[] = 'Invalid status. Allowed: ' . implode(', ', $allowedStatuses);
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $errors
    ]);
    exit();
}

$pdo = getDBConnection();

try {
    // --- Step 2: Verify task exists and belongs to user ---
    $stmt = $pdo->prepare("SELECT user_id, status FROM tasks WHERE task_id = ?");
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
        http_response_code(403); // 403 = Forbidden (you don't own this)
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to update this task'
        ]);
        exit();
    }

    // --- Step 3: Handle completion date logic ---
    // Same logic as your existing update_task.php:
    // - If newly completed → set completion_date
    // - If un-completed → clear completion_date
    $newStatus = isset($data->status) ? $data->status : $task['status'];
    $isNewlyCompleted = ($newStatus === 'Completed' && $task['status'] !== 'Completed');
    $isUncompleted = ($task['status'] === 'Completed' && $newStatus !== 'Completed');

    // Start a transaction to ensure all updates happen together
    $pdo->beginTransaction();

    if ($isNewlyCompleted) {
        $compStmt = $pdo->prepare("UPDATE tasks SET completion_date = CURRENT_TIMESTAMP WHERE task_id = ?");
        $compStmt->execute([$taskId]);
    }

    if ($isUncompleted) {
        $compStmt = $pdo->prepare("UPDATE tasks SET completion_date = NULL WHERE task_id = ?");
        $compStmt->execute([$taskId]);
    }

    // --- Step 4: Update the task ---
    $title = trim($data->title);
    $description = isset($data->description) ? trim($data->description) : null;
    $category = $data->category;
    $dueDate = $data->due_date;
    $priority = isset($data->priority) ? $data->priority : 'Medium';
    $status = $newStatus;
    $reminderEnabled = isset($data->reminder_enabled) && $data->reminder_enabled ? 1 : 0;

    $stmt = $pdo->prepare(
        "UPDATE tasks SET 
            title = ?, 
            description = ?, 
            due_date = ?, 
            category = ?, 
            priority = ?, 
            status = ?, 
            reminder_enabled = ?,
            last_updated_at = CURRENT_TIMESTAMP
         WHERE task_id = ? AND user_id = ?"
    );

    $result = $stmt->execute([
        $title, $description, $dueDate, $category, $priority, $status, $reminderEnabled, $taskId, $userId
    ]);

    if ($result) {
        $pdo->commit();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Task updated successfully' . ($isNewlyCompleted ? '. Task marked as completed.' : '')
        ]);
    } else {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update task. Please try again.'
        ]);
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}