<?php
session_start();
include '../includes/PDOconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Reset any previous errors
    unset($_SESSION['errors']);
    $_SESSION['errors'] = [];
    $has_error = false;
    
    // Get form data
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($fullname)) {
        $_SESSION['errors']['fullname'] = "Full name is required.";
        $has_error = true;
    }
    
    if (empty($email)) {
        $_SESSION['errors']['email'] = "Email is required.";
        $has_error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['errors']['email'] = "Invalid email format.";
        $has_error = true;
    }
    
    if (empty($password)) {
        $_SESSION['errors']['password'] = "Password is required.";
        $has_error = true;
    } elseif (strlen($password) < 6) {
        $_SESSION['errors']['password'] = "Password must be at least 6 characters long.";
        $has_error = true;
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['errors']['confirm_password'] = "Passwords do not match.";
        $has_error = true;
    }
    
    // If there are errors, redirect back to the form
    if ($has_error) {
        // Store valid form data to repopulate the form
        $_SESSION['form_data'] = [
            'fullname' => $fullname,
            'email' => $email
        ];
        header("Location: ../public/register-page.php");
        exit();
    }
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['errors']['email'] = "Email already exists. Please use a different email.";
            $_SESSION['form_data'] = [
                'fullname' => $fullname,
                'email' => $email
            ];
            header("Location: ../public/register-page.php");
            exit();
        }
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (NAME, email, PASSWORD, created_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$fullname, $email, $hashed_password]);
        
        if ($result) {
            $_SESSION['success'] = "Account created successfully. Please login.";
            header("Location: ../public/login-page.php");
            exit();
        } else {
            $_SESSION['errors']['general'] = "Something went wrong. Please try again.";
            header("Location: ../public/register-page.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['errors']['general'] = "Database error: " . $e->getMessage();
        header("Location: ../public/register-page.php");
        exit();
    }
} else {
    header("Location: ../public/register-page.php");
    exit();
}
?>