<?php
// ============================================================
// TaskHub API - Mark Notification as Read
// PUT /api/notifications/{id}
// ============================================================
//
// WHAT IT DOES:
// Marks a single notification as "Read". This is the API 
// equivalent of the 'mark_read' case in your 
// notification-actions.php.
//
// WHY PUT?
// We are updating an existing resource (changing its status
// from "Unread" to "Read"), so PUT is the correct HTTP method.
// No request body is needed — the action is implicit:
// "mark this notification as read."
//
// EXAMPLE REQUEST:
//   PUT /api/notifications/10
//   Headers: Authorization: Bearer eyJhbG...
//   (No body needed)
//
// EXAMPLE RESPONSE (200):
// {
//     "success": true,
//     "message": "Notification marked as read"
// }
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$userId = authenticate();

// Get notification ID from URL (set by router)
$notificationId = (int) $id;

if ($notificationId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid notification ID'
    ]);
    exit();
}

$pdo = getDBConnection();

try {
    // --- Step 1: Verify notification exists and belongs to user ---
    $stmt = $pdo->prepare(
        "SELECT notification_id, status FROM notifications WHERE notification_id = ? AND user_id = ?"
    );
    $stmt->execute([$notificationId, $userId]);
    $notification = $stmt->fetch();

    if (!$notification) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Notification not found'
        ]);
        exit();
    }

    // Check if already read — not an error, just let the client know
    if ($notification['status'] === 'Read') {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Notification was already marked as read'
        ]);
        exit();
    }

    // --- Step 2: Mark as read ---
    $stmt = $pdo->prepare(
        "UPDATE notifications SET status = 'Read' WHERE notification_id = ? AND user_id = ?"
    );
    $result = $stmt->execute([$notificationId, $userId]);

    if ($result) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update notification'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}