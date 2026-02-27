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
    $title = trim($_POST['title']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : NULL;
    $category = $_POST['category'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $reminder_enabled = isset($_POST['enable_reminder']) ? 1 : 0;
    $user_id = $_SESSION['user_id'];
    
    // Validate required fields
    if (empty($title) || empty($due_date)) {
        echo json_encode(["success" => false, "message" => "Title and due date are required."]);
        exit();
    }
    
    try {
        // Insert task into the database
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, category, priority, status, reminder_enabled) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $user_id, $title, $description, $due_date, $category, $priority, $status, $reminder_enabled
        ]);
        
        if ($result) {
            echo json_encode(["success" => true, "message" => "Task added successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error adding task. Please try again."]);
        }
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>