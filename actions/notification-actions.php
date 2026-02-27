<?php
require_once '../includes/auth-check.php';
require_once '../includes/PDOconn.php';
require_once '../includes/notification-functions.php';

$userId = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'mark_read':
        if (isset($_POST['notification_id'])) {
            $notificationId = (int)$_POST['notification_id'];
            $success = markNotificationAsRead($pdo, $notificationId, $userId);
            $response = [
                'success' => $success,
                'message' => $success ? 'Notification marked as read' : 'Failed to mark notification as read'
            ];
        }
        break;
        
    case 'mark_all_read':
        $success = markAllNotificationsAsRead($pdo, $userId);
        $response = [
            'success' => $success,
            'message' => $success ? 'All notifications marked as read' : 'Failed to mark all notifications as read'
        ];
        break;
        
    case 'delete':
        if (isset($_POST['notification_id'])) {
            $notificationId = (int)$_POST['notification_id'];
            $success = deleteNotification($pdo, $notificationId, $userId);
            $response = [
                'success' => $success,
                'message' => $success ? 'Notification deleted' : 'Failed to delete notification'
            ];
        }
        break;
        
    case 'delete_all':
        $success = deleteAllNotifications($pdo, $userId);
        $response = [
            'success' => $success,
            'message' => $success ? 'All notifications deleted' : 'Failed to delete all notifications'
        ];
        break;
        
    default:
        $response = ['success' => false, 'message' => 'Invalid action'];
}

header('Content-Type: application/json');
echo json_encode($response);