<?php

//functions for handling notifications in TaskHub
function getUserNotifications($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT n.notification_id, n.task_id, n.notify_time, n.message, n.status, n.created_at, 
               t.title as task_title
        FROM notifications n
        LEFT JOIN tasks t ON n.task_id = t.task_id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get unread notification count for a user
function getUnreadNotificationCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'Unread'");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

//Mark a notification as read
function markNotificationAsRead($pdo, $notificationId, $userId) {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET status = 'Read' 
        WHERE notification_id = ? AND user_id = ?
    ");
    return $stmt->execute([$notificationId, $userId]);
}


//Mark all notifications as read for a user
function markAllNotificationsAsRead($pdo, $userId) {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET status = 'Read' 
        WHERE user_id = ? AND status = 'Unread'
    ");
    return $stmt->execute([$userId]);
}


//Delete a notification
function deleteNotification($pdo, $notificationId, $userId) {
    $stmt = $pdo->prepare("
        DELETE FROM notifications 
        WHERE notification_id = ? AND user_id = ?
    ");
    return $stmt->execute([$notificationId, $userId]);
}


//Delete all notifications for a user
function deleteAllNotifications($pdo, $userId) {
    $stmt = $pdo->prepare("
        DELETE FROM notifications 
        WHERE user_id = ?
    ");
    return $stmt->execute([$userId]);
}


//Create a new task reminder notification
function createTaskNotification($pdo, $userId, $taskId, $taskTitle, $message = null) {
    if ($message === null) {
        $message = "Reminder: Task \"$taskTitle\" needs your attention.";
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, task_id, message, status, notify_time, created_at)
        VALUES (?, ?, ?, 'Unread', NOW(), NOW())
    ");
    return $stmt->execute([$userId, $taskId, $message]);
}

//Format notification time for display
function formatTimeAgo($dbTime) {
    $timestamp = strtotime($dbTime);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' ' . ($mins == 1 ? 'minute' : 'minutes') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ' . ($hours == 1 ? 'hour' : 'hours') . ' ago';
    } elseif ($diff < 604800) { // 7 days
        $days = floor($diff / 86400);
        return $days . ' ' . ($days == 1 ? 'day' : 'days') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}

// This function checks for tasks with reminders enabled and creates notifications
function sendDailyReminders($pdo) {
    // Get all non-completed tasks with reminders enabled
    $stmt = $pdo->prepare("
        SELECT t.task_id, t.title, t.due_date, t.user_id 
        FROM tasks t 
        WHERE t.status != 'Completed' 
        AND t.reminder_enabled = 1
        AND t.due_date >= CURDATE()
    ");
    
    $stmt->execute();
    $tasksToNotify = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 0;
    
    foreach ($tasksToNotify as $task) {
        // Calculate days until due
        $dueDate = new DateTime($task['due_date']);
        $today = new DateTime();
        $daysRemaining = $today->diff($dueDate)->days;
        
        // Create message based on due date
        if ($dueDate->format('Y-m-d') === $today->format('Y-m-d')) {
            $message = "URGENT: Task \"" . $task['title'] . "\" is due today!";
        } else {
            $message = "Reminder: Task \"" . $task['title'] . "\" is due in {$daysRemaining} " . 
                       ($daysRemaining == 1 ? "day" : "days") . ".";
        }
        
        // Check if we've already sent a notification for this task today
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE task_id = ? 
            AND DATE(created_at) = CURDATE()
        ");
        $checkStmt->execute([$task['task_id']]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        // Only send one notification per task per day
        if ($result['count'] == 0) {
            if (createTaskNotification($pdo, $task['user_id'], $task['task_id'], $task['title'], $message)) {
                $count++;
            }
        }
    }
    return $count;
}

// Check for tasks approaching auto-deletion and send notifications
function processTaskAutoDeletion($pdo) {
    // Number of days after which to auto-delete completed tasks
    $deleteAfterDays = 7;
    // Notification generation before deletion
    $notifyBeforeDays = 3;
    
    // Results counters
    $deletedCount = 0;
    $notificationCount = 0;
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Step 1: Send notifications for tasks that are approaching deletion
        $notifyStmt = $pdo->prepare("
            SELECT t.task_id, t.title, t.user_id, t.completion_date, 
                   DATEDIFF(DATE_ADD(t.completion_date, INTERVAL ? DAY), CURDATE()) as days_remaining
            FROM tasks t
            WHERE t.status = 'Completed' 
            AND t.archived = 0
            AND DATEDIFF(DATE_ADD(t.completion_date, INTERVAL ? DAY), CURDATE()) BETWEEN 1 AND ?
            AND NOT EXISTS (
                SELECT 1 FROM notifications n 
                WHERE n.task_id = t.task_id 
                AND n.message LIKE '%will be automatically deleted%'
                AND DATE(n.created_at) = CURDATE()
            )
        ");
        
        $notifyStmt->execute([$deleteAfterDays, $deleteAfterDays, $notifyBeforeDays]);
        $tasksToNotify = $notifyStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($tasksToNotify as $task) {
            $message = "Task \"{$task['title']}\" will be automatically deleted in {$task['days_remaining']} " . 
                     ($task['days_remaining'] == 1 ? "day" : "days") . ". Archive it if you want to keep it.";
            
            if (createTaskNotification($pdo, $task['user_id'], $task['task_id'], $task['title'], $message)) {
                $notificationCount++;
            }
        }
        
        // Step 2: Find tasks to delete and store their info BEFORE deleting them
        $tasksToDeleteStmt = $pdo->prepare("
            SELECT task_id, title, user_id
            FROM tasks 
            WHERE status = 'Completed' 
            AND archived = 0
            AND DATEDIFF(CURDATE(), completion_date) >= ?
        ");
        
        $tasksToDeleteStmt->execute([$deleteAfterDays]);
        $tasksToDelete = $tasksToDeleteStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Step 3: Delete the tasks
        if (!empty($tasksToDelete)) {
            $taskIds = array_column($tasksToDelete, 'task_id');
            $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
            
            $deleteStmt = $pdo->prepare("
                DELETE FROM tasks 
                WHERE task_id IN ($placeholders)
            ");
            
            $deleteStmt->execute($taskIds);
            $deletedCount = $deleteStmt->rowCount();
            
            // Step 4: Send notifications about the deleted tasks
            foreach ($tasksToDelete as $task) {
                $message = "Task \"{$task['title']}\" has been automatically deleted by the system after $deleteAfterDays days of completion.";
                
                if (createTaskNotification($pdo, $task['user_id'], NULL, 'System Notification', $message)) {
                    $notificationCount++;
                }
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        return [
            'deleted' => $deletedCount,
            'notifications' => $notificationCount
        ];
        
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Auto-deletion error: " . $e->getMessage());
        return [
            'deleted' => 0,
            'notifications' => 0,
            'error' => $e->getMessage()
        ];
    }
}

// Check if need to delete completed task today
function checkTaskAutoDeletion($pdo) {
    $lastAutoDeleteFile = __DIR__ . '/../last_auto_delete_date.txt';
    $today = date('Y-m-d');
    
    if (!file_exists($lastAutoDeleteFile) || file_get_contents($lastAutoDeleteFile) !== $today) {
        // It's a new day, process auto-deletion
        $result = processTaskAutoDeletion($pdo);
        
        // Update the last auto-delete date
        file_put_contents($lastAutoDeleteFile, $today);
        
        return $result;
    }
    
    return ['deleted' => 0, 'notifications' => 0, 'skipped' => true];
}