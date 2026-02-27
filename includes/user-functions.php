<?php
// User-related functions for TaskHub application
function getUserData($pdo, $userId) {
    $userData = [
        'name' => 'User',
        'email' => '',
        'department' => '',
        'profile_picture' => 'https://i.postimg.cc/k59vy0Lt/default-avatar-profile-icon-vector-social-media-user-image-182145777.png',
        'pending_count' => 0,
        'completed_count' => 0
    ];
    
    try {
        // Fetch user details
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If user data is found, set variables
        if ($user) {
            $userData['NAME'] = $user['NAME'];
            $userData['email'] = $user['email'];
            $userData['department'] = isset($user['department']) ? $user['department'] : '';
            $userData['profile_picture'] = isset($user['profile_picture']) && !empty($user['profile_picture']) 
                ? $user['profile_picture'] 
                : 'https://i.postimg.cc/k59vy0Lt/default-avatar-profile-icon-vector-social-media-user-image-182145777.png';
        }
        
        // Get task statistics
        $stmtPending = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'pending'");
        $stmtPending->execute([$userId]);
        $userData['pending_count'] = $stmtPending->fetchColumn();
        
        $stmtCompleted = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed'");
        $stmtCompleted->execute([$userId]);
        $userData['completed_count'] = $stmtCompleted->fetchColumn();
        
    } catch(PDOException $e) {
        // Log the error
        error_log("Error fetching user data: " . $e->getMessage());
    }
    
    return $userData;
}

// Update user profile information
function updateUserProfile($pdo, $userId, $name, $email, $department) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, department = ? WHERE user_id = ?");
        return $stmt->execute([$name, $email, $department, $userId]);
    } catch(PDOException $e) {
        error_log("Error updating user profile: " . $e->getMessage());
        return false;
    }
}

//Update user password
function updateUserPassword($pdo, $userId, $currentPassword, $newPassword) {
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    try {
        // First verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $storedPassword = $stmt->fetchColumn();
        
        if (password_verify($currentPassword, $storedPassword)) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $success = $stmt->execute([$hashedPassword, $userId]);
            
            if ($success) {
                $result['success'] = true;
                $result['message'] = "Password updated successfully!";
            } else {
                $result['message'] = "Database error while updating password.";
            }
        } else {
            $result['message'] = "Current password is incorrect!";
        }
    } catch(PDOException $e) {
        error_log("Error updating user password: " . $e->getMessage());
        $result['message'] = "Database error: " . $e->getMessage();
    }
    
    return $result;
}