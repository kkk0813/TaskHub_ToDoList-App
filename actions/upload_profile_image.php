<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] > 0) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

// Include database connection
include '../includes/PDOconn.php';

// Get user ID
$userId = $_SESSION['user_id'];

// Create upload directory if it doesn't exist
$uploadDir = 'uploads/profile_pictures/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate a unique filename
$fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
$newFilename = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
$targetFile = $uploadDir . $newFilename;

// Check file type
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array(strtolower($fileExtension), $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: jpg, jpeg, png, gif']);
    exit();
}

// Check file size (max 5MB)
if ($_FILES['profile_image']['size'] > 5000000) {
    echo json_encode(['success' => false, 'message' => 'File is too large (max 5MB)']);
    exit();
}

// Move the uploaded file
if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
    try {
        // Update user profile in database
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->execute([$targetFile, $userId]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture updated successfully', 
            'image_url' => $targetFile
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save the uploaded file']);
}
?>