<?php
session_start();
include '../includes/PDOconn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $taskId = (int)$_POST['task_id'];
    $title = trim($_POST['title']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : NULL;
    $category = $_POST['category'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $reminder_enabled = isset($_POST['enable_reminder']) ? 1 : 0;
    $user_id = $_SESSION['user_id'];
    
    // Validate required fields
    if (empty($title) || empty($due_date) || $taskId <= 0) {
        echo json_encode(["success" => false, "message" => "Title and due date are required."]);
        exit();
    }
    
    try {
        // Start a transaction to ensure data consistency
        $pdo->beginTransaction();
        
        // Verify the task belongs to the current user and get current status
        $stmt = $pdo->prepare("SELECT user_id, status FROM tasks WHERE task_id = ?");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$task || $task['user_id'] != $user_id) {
            echo json_encode(["success" => false, "message" => "You don't have permission to edit this task."]);
            $pdo->rollBack();
            exit();
        }
        
        // Check if task is being marked as completed for the first time
        $isNewlyCompleted = ($status === 'Completed' && $task['status'] !== 'Completed');
        
        // Update the completion_date if task is newly completed
        if ($isNewlyCompleted) {
            $updateCompletionDate = $pdo->prepare("
                UPDATE tasks 
                SET completion_date = CURRENT_TIMESTAMP 
                WHERE task_id = ?
            ");
            $updateCompletionDate->execute([$taskId]);
        }
        
        // Reset completion_date if task is being changed from completed to other status
        if ($task['status'] === 'Completed' && $status !== 'Completed') {
            $resetCompletionDate = $pdo->prepare("
                UPDATE tasks 
                SET completion_date = NULL 
                WHERE task_id = ?
            ");
            $resetCompletionDate->execute([$taskId]);
        }
        
        // Update the task in the database
        $stmt = $pdo->prepare("UPDATE tasks SET 
                title = ?, 
                description = ?, 
                due_date = ?, 
                category = ?, 
                priority = ?, 
                status = ?, 
                reminder_enabled = ?,
                last_updated_at = CURRENT_TIMESTAMP
                WHERE task_id = ? AND user_id = ?");
        
        $result = $stmt->execute([
            $title, $description, $due_date, $category, $priority, $status, $reminder_enabled, $taskId, $user_id
        ]);
        
        if ($result) {
            // Commit the transaction
            $pdo->commit();
            echo json_encode(["success" => true, "message" => "Task updated successfully!" . ($isNewlyCompleted ? " Task marked as completed." : "")]);
        } else {
            $pdo->rollBack();
            echo json_encode(["success" => false, "message" => "Error updating task. Please try again."]);
        }
    } catch(PDOException $e) {
        // Roll back the transaction on error
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>