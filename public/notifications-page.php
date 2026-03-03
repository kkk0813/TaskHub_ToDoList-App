<?php
// Include authentication check
require_once '../includes/auth-check.php';
// Get user data from database
include '../includes/PDOconn.php';

$activePage = 'notifications';
require_once '../includes/user-functions.php';
require_once '../includes/notification-functions.php';

$userData = getUserData($pdo, $_SESSION['user_id']);
$userName = $userData['NAME'];
$profilePicture = $userData['profile_picture'];

// Get user notifications
$notifications = getUserNotifications($pdo, $_SESSION['user_id']);
$hasNotifications = !empty($notifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHub - Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboardstyle.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <div class="logo"><i class="fas fa-check" style="color: white;"></i></div>
            <div class="logo-text">TaskHub</div>
        </div>
        <div class="user-menu">
            <div class="profile">
                <div class="profile-pic" style="display: flex; padding: 3px;">
                    <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="profile">
                </div>
                <div class="profile-name">Welcome, <?php echo htmlspecialchars($userName); ?></div>
            </div>
            <div class="logout-btn" id="logoutButton">
                <i class="fas fa-sign-out-alt"></i> Logout
            </div>
        </div>
    </div>

    <div class="main-container">
        <?php include '../components/sidebar.php'; ?>

        <div class="content">
            <div class="content-header">
                <h1 class="content-title">Notifications</h1>
                <div class="notification-actions">
                    <button class="btn-mark-all-read">Mark All as Read</button>
                    <button class="btn-clear-all">Clear All</button>
                </div>
            </div>

            <!-- Notifications Container -->
            <div class="notification-container">
                <?php if ($hasNotifications): ?>
                    <div class="notification-list">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-card <?php echo $notification['status'] === 'Unread' ? 'unread' : ''; ?>" 
                                 data-id="<?php echo $notification['notification_id']; ?>">
                                <div class="notification-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-header">
                                        <span class="notification-title">Task Reminder</span>
                                        <span class="notification-time"><?php echo formatTimeAgo($notification['notify_time']); ?></span>
                                    </div>
                                    <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <?php if (!empty($notification['task_id'])): ?>
                                    <div class="notification-actions">
                                        <a href="../actions/get_task.php?id=<?php echo $notification['task_id']; ?>" class="notification-link">View Task</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="notification-options">
                                    <button class="notification-option-btn"><i class="fas fa-ellipsis-v"></i></button>
                                    <div class="notification-option-menu">
                                        <?php if ($notification['status'] === 'Unread'): ?>
                                        <a href="#" class="mark-read" data-id="<?php echo $notification['notification_id']; ?>">Mark as Read</a>
                                        <?php endif; ?>
                                        <a href="#" class="remove" data-id="<?php echo $notification['notification_id']; ?>">Remove</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State for Notifications -->
                    <div class="empty-state" style="display: flex;">
                        <div class="empty-state-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h3 class="empty-state-title">No Notifications</h3>
                        <p class="empty-state-message">You don't have any notifications at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>This application is fictitious and is part of a university course. © 2025 TaskHub</footer>

    <!-- Add task modal for viewing task details -->
    <?php include '../components/task-modal.php'; ?>
    <?php include '../components/confirmation-dialog.php'; ?>
    
    <script src="../assets/js/api-helper.js"></script> 
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/logoutscript.js"></script>
    <script src="../assets/js/viewtask.js"></script>
    <script src="../assets/js/notificationscript.js"></script>
</body>
</html>