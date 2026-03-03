<?php
// ============================================================
// TaskHub API - List All Notifications
// GET /api/notifications
// ============================================================
//
// WHAT IT DOES:
// Returns all notifications for the logged-in user, ordered
// by newest first. This is the API equivalent of the query
// in your notifications-page.php.
//
// EXAMPLE REQUEST:
//   GET /api/notifications
//   Headers: Authorization: Bearer eyJhbG...
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$userId = authenticate();

$pdo = getDBConnection();

try {
    // Get all notifications with task titles via LEFT JOIN
    // LEFT JOIN means we still get notifications even if the
    // related task has been deleted (task_title would be null)
    $stmt = $pdo->prepare("
        SELECT n.notification_id, n.task_id, n.notify_time, n.message, 
               n.status, n.created_at, t.title AS task_title
        FROM notifications n
        LEFT JOIN tasks t ON n.task_id = t.task_id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
    ");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();

    // Count unread notifications as a convenience
    // so the client can display a badge like "3 unread"
    $unreadStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'Unread'"
    );
    $unreadStmt->execute([$userId]);
    $unreadCount = (int) $unreadStmt->fetchColumn();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($notifications),
        'unread_count' => $unreadCount,
        'notifications' => $notifications
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}