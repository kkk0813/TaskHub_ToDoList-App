<?php
/**
 * Authentication check to be included at the top of all protected pages
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header("Location: ../public/login-page.php");
    exit();
}

// Display the user's name
$userName = isset($_SESSION['name']) ? $_SESSION['name'] : 'User';