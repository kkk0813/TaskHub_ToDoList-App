<?php
// ============================================================
// TaskHub API - Update Profile
// PUT /api/profile
// ============================================================
//
// WHAT IT DOES:
// Updates the logged-in user's profile information or password.
// This is the API equivalent of your profile-update.php.
//
// The client specifies which action via an "action" field:
//   "update_profile" - change name, email, department
//   "update_password" - change password
//
// === UPDATE PROFILE ===
// {
//     "action": "update_profile",
//     "fullname": "New Name",
//     "email": "new@email.com",
//     "department": "Computer Science"
// }
//
// === UPDATE PASSWORD ===
// {
//     "action": "update_password",
//     "current_password": "oldpass123",
//     "new_password": "newpass456",
//     "confirm_password": "newpass456"
// }
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$userId = authenticate();

$data = json_decode(file_get_contents('php://input'));

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON. Please send a valid JSON body.'
    ]);
    exit();
}

if (empty($data->action)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Action is required. Use "update_profile" or "update_password".'
    ]);
    exit();
}

$pdo = getDBConnection();

switch ($data->action) {

    case 'update_profile':
        $errors = [];

        if (empty($data->fullname)) {
            $errors[] = 'Full name is required';
        }
        if (empty($data->email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ]);
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([trim($data->email), $userId]);

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'message' => 'Email is already used by another account.'
                ]);
                exit();
            }

            $department = isset($data->department) ? trim($data->department) : '';
            $stmt = $pdo->prepare("UPDATE users SET NAME = ?, email = ?, department = ? WHERE user_id = ?");
            $result = $stmt->execute([trim($data->fullname), trim($data->email), $department, $userId]);

            if ($result) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'user' => [
                        'name' => trim($data->fullname),
                        'email' => trim($data->email),
                        'department' => $department
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'update_password':
        $errors = [];

        if (empty($data->current_password)) {
            $errors[] = 'Current password is required';
        }
        if (empty($data->new_password)) {
            $errors[] = 'New password is required';
        } elseif (strlen($data->new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
        if (empty($data->confirm_password)) {
            $errors[] = 'Confirm password is required';
        } elseif ($data->new_password !== $data->confirm_password) {
            $errors[] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ]);
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT PASSWORD FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $storedPassword = $stmt->fetchColumn();

            if (!password_verify($data->current_password, $storedPassword)) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                exit();
            }

            $hashedPassword = password_hash($data->new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET PASSWORD = ? WHERE user_id = ?");
            $result = $stmt->execute([$hashedPassword, $userId]);

            if ($result) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action. Use "update_profile" or "update_password".'
        ]);
        break;
}