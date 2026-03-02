<?php
// ============================================================
// TaskHub API - Create Task
// POST /api/tasks
// ============================================================
//
// WHAT IT DOES:
// Creates a new task for the logged-in user.
// This is the API equivalent of your add_task.php.
//
// WHAT THE CLIENT SENDS (JSON body):
// {
//     "title": "Finish assignment",        <- required
//     "description": "Chapter 5 exercises", <- optional
//     "category": "Assignment",             <- required
//     "due_date": "2025-04-01",            <- required
//     "priority": "High",                   <- optional (defaults to Medium)
//     "status": "Pending",                  <- optional (defaults to Pending)
//     "reminder_enabled": true              <- optional (defaults to false)
// }
//
// EXAMPLE RESPONSE (201 Created):
// {
//     "success": true,
//     "message": "Task created successfully",
//     "task_id": 15
// }
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$userId = authenticate();

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

// Validate category against allowed values
// This matches your database ENUM: 'Assignment','Discussion','Club Activity','Examination'
$allowedCategories = ['Assignment', 'Discussion', 'Club Activity', 'Examination'];
if (!empty($data->category) && !in_array($data->category, $allowedCategories)) {
    $errors[] = 'Invalid category. Allowed: ' . implode(', ', $allowedCategories);
}

// Validate priority if provided
$allowedPriorities = ['High', 'Medium', 'Low'];
if (!empty($data->priority) && !in_array($data->priority, $allowedPriorities)) {
    $errors[] = 'Invalid priority. Allowed: ' . implode(', ', $allowedPriorities);
}

// Validate status if provided
$allowedStatuses = ['Pending', 'On-going', 'Completed'];
if (!empty($data->status) && !in_array($data->status, $allowedStatuses)) {
    $errors[] = 'Invalid status. Allowed: ' . implode(', ', $allowedStatuses);
}

// Validate date format
if (!empty($data->due_date) && !strtotime($data->due_date)) {
    $errors[] = 'Invalid date format. Use YYYY-MM-DD';
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

// --- Step 2: Set default values for optional fields ---
$title = trim($data->title);
$description = isset($data->description) ? trim($data->description) : null;
$category = $data->category;
$dueDate = $data->due_date;
$priority = isset($data->priority) ? $data->priority : 'Medium';
$status = isset($data->status) ? $data->status : 'Pending';
$reminderEnabled = isset($data->reminder_enabled) && $data->reminder_enabled ? 1 : 0;

// --- Step 3: Insert into database ---
$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare(
        "INSERT INTO tasks (user_id, title, description, due_date, category, priority, status, reminder_enabled) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $result = $stmt->execute([
        $userId, $title, $description, $dueDate, $category, $priority, $status, $reminderEnabled
    ]);

    if ($result) {
        // lastInsertId() returns the auto-incremented task_id of the new row
        // This is useful for the client if they want to immediately
        // fetch or reference the task they just created
        http_response_code(201); // 201 = Created
        echo json_encode([
            'success' => true,
            'message' => 'Task created successfully',
            'task_id' => (int) $pdo->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create task. Please try again.'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}