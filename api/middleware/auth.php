<?php
// ============================================================
// TaskHub API - Authentication Middleware
// ============================================================
//
// WHAT IS MIDDLEWARE?
// Middleware is code that runs BEFORE your main endpoint logic.
// Think of it as a security guard at a door — it checks your 
// credentials before letting you in.
//
// HOW ENDPOINTS USE IT:
//   require_once __DIR__ . '/../middleware/auth.php';
//   $userId = authenticate();  // Returns user ID or kills request
//   // If we get here, the user is verified
//
// HOW THE CLIENT SENDS THE TOKEN:
// In the request headers:
//   Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
//
// The word "Bearer" is a standard convention that means 
// "I'm bearing (carrying) this token as proof of identity."
// ============================================================

require_once __DIR__ . '/../config/jwt.php';

// Load Composer's autoloader — this makes the JWT library available
// Composer generates this file; it handles loading all installed packages
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

/**
 * Authenticate the request by verifying the JWT token.
 * 
 * @return int The authenticated user's ID
 * 
 * If the token is missing, invalid, or expired, this function 
 * immediately sends an error response and stops execution.
 * If valid, it returns the user_id from the token payload.
 */
function authenticate() {
    // --- Step 1: Get the Authorization header ---
    $headers = getAuthorizationHeader();

    if (empty($headers)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. No token provided. Send your token in the Authorization header as: Bearer <your-token>'
        ]);
        exit();
    }

    // --- Step 2: Extract the token from "Bearer <token>" ---
    // Split "Bearer eyJhbGci..." into ["Bearer", "eyJhbGci..."]
    $parts = explode(' ', $headers);

    if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid Authorization header format. Use: Bearer <your-token>'
        ]);
        exit();
    }

    $token = $parts[1];

    // --- Step 3: Decode and verify the token ---
    try {
        // JWT::decode does three things:
        // 1. Decodes the base64 payload
        // 2. Verifies the signature using our secret key
        // 3. Checks that the token hasn't expired
        // If any of these fail, it throws an exception
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));

        // Token is valid — return the user ID from the payload
        return (int) $decoded->user_id;

    } catch (ExpiredException $e) {
        // Token was valid but has expired
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Token has expired. Please login again.'
        ]);
        exit();

    } catch (\Exception $e) {
        // Token is invalid (bad signature, malformed, etc.)
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid token. Please login again.'
        ]);
        exit();
    }
}

/**
 * Get the Authorization header from the request.
 * 
 * Different servers provide this header in different ways,
 * so we check multiple methods to be compatible with various setups.
 */
function getAuthorizationHeader() {
    // Method 1: Direct from apache_request_headers()
    // This works on most Apache setups
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        // Header names can be any case, so we check common variations
        if (isset($headers['Authorization'])) {
            return $headers['Authorization'];
        }
        if (isset($headers['authorization'])) {
            return $headers['authorization'];
        }
    }

    // Method 2: From $_SERVER (works on most setups)
    // Apache sometimes puts it here with a "HTTP_" prefix
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return $_SERVER['HTTP_AUTHORIZATION'];
    }

    // Method 3: Some Apache configurations use this instead
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    return null;
}