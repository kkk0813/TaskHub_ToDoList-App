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
                                        <a href="get_task.php?id=<?php echo $notification['task_id']; ?>" class="notification-link">View Task</a>
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
    
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/logoutscript.js"></script>
    <script src="../assets/js/viewtask.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle notification option menu
            const optionBtns = document.querySelectorAll('.notification-option-btn');
            
            optionBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const menu = this.nextElementSibling;
                    if (menu) {
                        // Close all other menus first
                        document.querySelectorAll('.notification-option-menu').forEach(m => {
                            if (m !== menu) m.classList.remove('show');
                        });
                        // Toggle this menu
                        menu.classList.toggle('show');
                    }
                });
            });
            
            // Close menus when clicking elsewhere
            document.addEventListener('click', function() {
                document.querySelectorAll('.notification-option-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            });
            
            // Mark as read functionality
            const markReadLinks = document.querySelectorAll('.mark-read');
            markReadLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const notificationId = this.getAttribute('data-id');
                    const card = this.closest('.notification-card');
                    
                    // Send AJAX request to mark as read
                    fetch('../actions/notification-actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=mark_read&notification_id=' + notificationId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            card.classList.remove('unread');
                            showAlert('Notification marked as read', 'success');
                        } else {
                            showAlert(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('An error occurred', 'error');
                    });
                });
            });
            
            // Remove notification functionality
            const removeLinks = document.querySelectorAll('.remove');
            removeLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const notificationId = this.getAttribute('data-id');
                    const card = this.closest('.notification-card');
                    
                    // Send AJAX request to delete notification
                    fetch('../actions/notification-actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=delete&notification_id=' + notificationId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            card.remove();
                            showAlert('Notification removed', 'success');
                            
                            // Check if there are any notifications left
                            if (document.querySelectorAll('.notification-card').length === 0) {
                                // Show empty state
                                const container = document.querySelector('.notification-container');
                                container.innerHTML = `
                                    <div class="empty-state" style="display: flex;">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-bell-slash"></i>
                                        </div>
                                        <h3 class="empty-state-title">No Notifications</h3>
                                        <p class="empty-state-message">You don't have any notifications at the moment.</p>
                                    </div>
                                `;
                            }
                        } else {
                            showAlert(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('An error occurred', 'error');
                    });
                });
            });
            
            // Mark all as read button
            const markAllBtn = document.querySelector('.btn-mark-all-read');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function() {
                    // Send AJAX request to mark all as read
                    fetch('../actions/notification-actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=mark_all_read'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelectorAll('.notification-card.unread').forEach(card => {
                                card.classList.remove('unread');
                            });
                            showAlert('All notifications marked as read', 'success');
                        } else {
                            showAlert(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('An error occurred', 'error');
                    });
                });
            }
            
            // Clear all notifications button
            const clearAllBtn = document.querySelector('.btn-clear-all');
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function() {
                    // Use the custom confirmation dialog instead of browser confirm
                    showConfirmDialog('Are you sure you want to delete all notifications?', function(confirmed) {
                        if (confirmed) {
                            // Send AJAX request to delete all notifications
                            fetch('../actions/notification-actions.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'action=delete_all'
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Show empty state
                                    const container = document.querySelector('.notification-container');
                                    container.innerHTML = `
                                        <div class="empty-state" style="display: flex;">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-bell-slash"></i>
                                            </div>
                                            <h3 class="empty-state-title">No Notifications</h3>
                                            <p class="empty-state-message">You don't have any notifications at the moment.</p>
                                        </div>
                                    `;
                                    
                                    showAlert('All notifications cleared', 'success');
                                } else {
                                    showAlert(data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showAlert('An error occurred', 'error');
                            });
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>