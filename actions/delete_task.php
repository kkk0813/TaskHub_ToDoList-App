<?php
session_start();
include '../includes/PDOconn.php'; // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

// Check if task_id is set
if (!isset($_POST["task_id"])) {
    echo json_encode(["success" => false, "message" => "Task ID not provided"]);
    exit();
}

$task_id = $_POST["task_id"];
$user_id = $_SESSION['user_id'];

try {
    // Verify the task belongs to the current user
    $checkStmt = $pdo->prepare("SELECT user_id FROM tasks WHERE task_id = ?");
    $checkStmt->execute([$task_id]);
    $task = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        echo json_encode(["success" => false, "message" => "Task not found"]);
        exit();
    }
    
    if ($task['user_id'] != $user_id) {
        echo json_encode(["success" => false, "message" => "You don't have permission to delete this task"]);
        exit();
    }
    
    // Delete the task
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE task_id = ? AND user_id = ?");
    $result = $stmt->execute([$task_id, $user_id]);
    
    if ($result) {
        echo json_encode(["success" => true, "message" => "Task deleted successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error deleting task"]);
    }
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>