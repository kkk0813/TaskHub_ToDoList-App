<?php
// ============================================================
// TaskHub API - Upload Profile Picture
// POST /api/profile/picture
// ============================================================
//
// Uploads a new profile picture for the logged-in user.
// Unlike JSON endpoints, file uploads use "multipart/form-data".
// In Postman, select "form-data" in Body tab, add key
// "profile_image" with type "File", then select your image.
//
// EXAMPLE RESPONSE (200):
// {
//     "success": true,
//     "message": "Profile picture updated successfully",
//     "image_url": "uploads/profile_pictures/user_1_1711234567.jpg"
// }
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$userId = authenticate();

// Check if a file was uploaded
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] > 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No file uploaded or upload error. Send a file with the key "profile_image".'
    ]);
    exit();
}

// Validate file type
$fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

if (!in_array($fileExtension, $allowedTypes)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)
    ]);
    exit();
}

// Validate file size (max 5MB)
if ($_FILES['profile_image']['size'] > 5000000) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'File is too large. Maximum size: 5MB.'
    ]);
    exit();
}

// Save the file - go up two levels from api/profile/ to project root
$uploadDir = __DIR__ . '/../../uploads/profile_pictures/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$newFilename = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
$targetFile = $uploadDir . $newFilename;
$dbPath = '../uploads/profile_pictures/' . $newFilename;

if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
    $pdo = getDBConnection();

    try {
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->execute([$dbPath, $userId]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'image_url' => $dbPath
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save the uploaded file.'
    ]);
}