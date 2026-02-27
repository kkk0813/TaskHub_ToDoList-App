<?php
session_start();
require_once '../includes/PDOconn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check if task ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No task ID provided']);
    exit();
}

$taskId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

try {
    // Get task details
    $stmt = $pdo->prepare("
        SELECT * 
        FROM tasks 
        WHERE task_id = ? AND user_id = ?
    ");
    $stmt->execute([$taskId, $userId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($task) {
        // Format the due date for the date input field (YYYY-MM-DD)
        if (isset($task['due_date'])) {
            $task['due_date'] = date('Y-m-d', strtotime($task['due_date']));
        }
        
        echo json_encode(['success' => true, 'task' => $task]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Task not found or access denied']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}