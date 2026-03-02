<?php
// ============================================================
// TaskHub API - Login Endpoint
// POST /api/auth/login
// ============================================================
//
// WHAT IT DOES:
// Verifies email + password, then returns a JWT token.
// This is the API equivalent of your existing login.php,
// but instead of creating a session and redirecting, 
// it returns a token the client must save and send back.
//
// WHAT THE CLIENT SENDS (JSON body):
// {
//     "email": "john@example.com",
//     "password": "secret123"
// }
//
// WHAT WE RETURN:
// Success (200): { "success": true, "token": "eyJhbG...", "user": {...} }
// Error (401):   { "success": false, "message": "Invalid credentials" }
//
// THE CLIENT THEN:
// 1. Saves the token (e.g., in localStorage or a variable)
// 2. Includes it in every future request as:
//    Authorization: Bearer eyJhbG...
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';

// Load Composer's autoloader to use the JWT library
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;

// --- Step 1: Read the JSON input ---
$data = json_decode(file_get_contents('php://input'));

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON. Please send a valid JSON body.'
    ]);
    exit();
}

// --- Step 2: Validate input ---
if (empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required.'
    ]);
    exit();
}

// --- Step 3: Find the user by email ---
$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([trim($data->email)]);
    $user = $stmt->fetch();

    // --- Step 4: Verify the password ---
    // password_verify() checks if the plain text password matches
    // the hashed version stored in the database.
    // We use the same error message for "user not found" and 
    // "wrong password" to prevent attackers from discovering 
    // which emails are registered (security best practice).
    if (!$user || !password_verify($data->password, $user['PASSWORD'])) {
        http_response_code(401); // 401 = Unauthorized
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password.'
        ]);
        exit();
    }

    // --- Step 5: Create the JWT token ---
    // The payload contains claims (pieces of information).
    // Standard claims:
    //   iss = issuer (who created the token)
    //   iat = issued at (when it was created — Unix timestamp)
    //   exp = expires (when it becomes invalid — Unix timestamp)
    // Custom claims:
    //   user_id = so we know who this token belongs to
    $issuedAt = time();
    $expiresAt = $issuedAt + JWT_EXPIRY; // current time + 3600 seconds

    $payload = [
        'iss' => JWT_ISSUER,           // "taskhub-api"
        'iat' => $issuedAt,            // e.g., 1711234567
        'exp' => $expiresAt,           // e.g., 1711238167
        'user_id' => $user['user_id']  // e.g., 1
    ];

    // JWT::encode creates the token string from the payload
    // using our secret key and the HS256 signing algorithm.
    // HS256 = HMAC-SHA256, a common and secure signing method.
    $token = JWT::encode($payload, JWT_SECRET, 'HS256');

    // --- Step 6: Return the token and user info ---
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'expires_in' => JWT_EXPIRY, // Tell client when to refresh
        'user' => [
            'user_id' => $user['user_id'],
            'name' => $user['NAME'],
            'email' => $user['email'],
            'department' => $user['department']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}