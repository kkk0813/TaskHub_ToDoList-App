<?php
session_start();
include 'PDOconn.php'; // Include your database connection

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../public/login-page.php");
        exit();
    }
    
    try {
        // Prepare a statement to check if the email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['PASSWORD'])) {
            // Authentication successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['NAME'];
            
            // Redirect to dashboard or home page
            header("Location: ../public/dashboard-page.php");
            exit();
        } else {
            // Authentication failed
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: ../public/login-page.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../public/login-page.php");
        exit();
    }
} else {
    // If not a POST request, redirect to login page
    header("Location: ../public/login-page.php");
    exit();
}
?>