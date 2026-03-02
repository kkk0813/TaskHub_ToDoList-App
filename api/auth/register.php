<?php
// ============================================================
// TaskHub API - Register Endpoint
// POST /api/auth/register
// ============================================================
//
// WHAT IT DOES:
// Creates a new user account. This is similar to your existing
// process-register.php, but instead of redirecting to a page,
// it returns a JSON response.
//
// WHAT THE CLIENT SENDS (JSON body):
// {
//     "fullname": "John Doe",
//     "email": "john@example.com",
//     "password": "secret123",
//     "confirm_password": "secret123"
// }
//
// WHAT WE RETURN:
// Success (201): { "success": true, "message": "Account created..." }
// Error (400):   { "success": false, "message": "Email already exists" }
// ============================================================

require_once __DIR__ . '/../config/database.php';

// --- Step 1: Read the JSON input ---
// In the web app, form data comes from $_POST.
// In an API, clients send data as a JSON string in the request body.
// file_get_contents('php://input') reads that raw JSON string,
// then json_decode() converts it into a PHP object.
$data = json_decode(file_get_contents('php://input'));

// Check if JSON was actually sent and is valid
if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON. Please send a valid JSON body.'
    ]);
    exit();
}

// --- Step 2: Validate the input ---
// We check each required field and collect all errors at once
// so the client can fix everything in one go (better UX).
$errors = [];

if (empty($data->fullname)) {
    $errors[] = 'Full name is required';
}

if (empty($data->email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($data->password)) {
    $errors[] = 'Password is required';
} elseif (strlen($data->password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}

if (empty($data->confirm_password)) {
    $errors[] = 'Confirm password is required';
} elseif ($data->password !== $data->confirm_password) {
    $errors[] = 'Passwords do not match';
}

// If there are validation errors, return them all
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $errors
    ]);
    exit();
}

// --- Step 3: Check if email already exists ---
$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([trim($data->email)]);

    if ($stmt->rowCount() > 0) {
        http_response_code(409); // 409 Conflict — resource already exists
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists. Please use a different email.'
        ]);
        exit();
    }

    // --- Step 4: Create the user ---
    // password_hash() creates a secure one-way hash of the password.
    // We never store the actual password — only the hash.
    $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (NAME, email, PASSWORD, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([
        trim($data->fullname),
        trim($data->email),
        $hashedPassword
    ]);

    if ($result) {
        // 201 = Created — the standard code when a new resource is made
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully. You can now login.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create account. Please try again.'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}