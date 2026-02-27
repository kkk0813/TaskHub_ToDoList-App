<?php
session_start();
require_once '../includes/PDOconn.php';
require_once '../includes/user-functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login-page.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Reset any previous errors
    unset($_SESSION['errors']);
    $_SESSION['errors'] = [];
    $has_error = false;
    
    // Process personal information update
    if (isset($_POST['update_profile'])) {
        // Get form data
        $newName = trim($_POST['fullName']);
        $newEmail = trim($_POST['email']);
        $newDepartment = trim($_POST['department']);
        
        // Validate input
        if (empty($newName)) {
            $_SESSION['errors']['fullName'] = "Full name is required.";
            $has_error = true;
        }
        
        if (empty($newEmail)) {
            $_SESSION['errors']['email'] = "Email is required.";
            $has_error = true;
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors']['email'] = "Invalid email format.";
            $has_error = true;
        }
        
        // If there are errors, redirect back to the form
        if ($has_error) {
            $_SESSION['error'] = "Please fix the errors in the form.";
            header("Location: ../public/profile-page.php");
            exit();
        }
        
        // Update profile
        if (updateUserProfile($pdo, $_SESSION['user_id'], $newName, $newEmail, $newDepartment)) {
            // Update session data
            $_SESSION['NAME'] = $newName;
            $_SESSION['success'] = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Could not update profile. Please try again.";
        }
    }
    
    // Process password update
    else if (isset($_POST['update_password'])) {
        // Get form data
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];
        // Validate input
        if (empty($currentPassword)) {
            $_SESSION['errors']['currentPassword'] = "Current password is required.";
            $has_error = true;
        }
        if (empty($newPassword)) {
            $_SESSION['errors']['newPassword'] = "New password is required.";
            $has_error = true;
        } elseif (strlen($newPassword) < 6) {
            $_SESSION['errors']['newPassword'] = "Password must be at least 6 characters long.";
            $has_error = true;
        }
        if ($newPassword !== $confirmPassword) {
            $_SESSION['errors']['confirmPassword'] = "Passwords do not match.";
            $has_error = true;
        }
        
        // If there are errors, redirect back to the form
        if ($has_error) {
            $_SESSION['error'] = "Please fix the errors in the form.";
            header("Location: ../public/profile-page.php");
            exit();
        }
        
        // Update password
        $result = updateUserPassword($pdo, $_SESSION['user_id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
    }
    // If no valid action was specified
    else {
        $_SESSION['error'] = "Invalid action.";
    }
    
    // Redirect to avoid form resubmission
    header("Location: ../public/profile-page.php");
    exit();
} else {
    header("Location: ../public/profile-page.php");
    exit();
}
?>