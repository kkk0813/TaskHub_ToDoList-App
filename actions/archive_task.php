<?php
session_start();
include '../includes/PDOconn.php';

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
    // First check if the task exists and is completed
    $checkStmt = $pdo->prepare("SELECT status FROM tasks WHERE task_id = ? AND user_id = ?");
    $checkStmt->execute([$task_id, $user_id]);
    $task = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        echo json_encode(["success" => false, "message" => "Task not found"]);
        exit();
    }
    
    if ($task['status'] !== 'Completed') {
        echo json_encode(["success" => false, "message" => "Only completed tasks can be archived"]);
        exit();
    }
    
    // Update task to archived
    $stmt = $pdo->prepare("UPDATE tasks SET archived = 1 WHERE task_id = ? AND user_id = ?");
    $result = $stmt->execute([$task_id, $user_id]);
    
    if ($result) {
        echo json_encode(["success" => true, "message" => "Task archived successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error archiving task"]);
    }
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>