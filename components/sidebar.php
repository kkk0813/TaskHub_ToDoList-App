<?php
$activePage = isset($activePage) ? $activePage : '';

// Get unread notification count
require_once 'includes/notification-functions.php';
$unreadCount = 0;
if (function_exists('getUnreadNotificationCount')) {
    $unreadCount = getUnreadNotificationCount($pdo, $_SESSION['user_id']);
}
?>
<div class="sidebar">
    <div class="sidebar-section">
        <div class="sidebar-title">Task Status</div>
        <ul class="sidebar-menu">
            <li <?php echo ($activePage == 'dashboard') ? 'class="active"' : 'onclick="window.location.href=\'dashboard-page.php\'"'; ?>><i class="fas fa-tasks"></i> All Tasks</li>
            <li <?php echo ($activePage == 'pendingtasks') ? 'class="active"' : 'onclick="window.location.href=\'pending-tasks-page.php\'"'; ?>><i class="fas fa-clock"></i> Pending</li>
            <li <?php echo ($activePage == 'ongoingtasks') ? 'class="active"' : 'onclick="window.location.href=\'ongoing-tasks-page.php\'"'; ?>><i class="fas fa-spinner"></i> On-going</li>
            <li <?php echo ($activePage == 'completedtasks') ? 'class="active"' : 'onclick="window.location.href=\'completed-tasks-page.php\'"'; ?>><i class="fas fa-check-circle"></i> Completed</li>
            <li <?php echo ($activePage == 'archivedtasks') ? 'class="active"' : 'onclick="window.location.href=\'archived-tasks-page.php\'"'; ?>><i class="fas fa-archive"></i> Archived</li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-title">Filters</div>
        <div class="filter-options">
            <div class="filter-option">
                <label class="filter-label" for="category-filter">Category</label>
                <select class="filter-select" id="category-filter">
                    <option value="">All Categories</option>
                    <option value="assignments">Assignments</option>
                    <option value="discussions">Discussions</option>
                    <option value="club">Club Activities</option>
                    <option value="exams">Examinations</option>
                </select>
            </div>
            <div class="filter-option">
                <label class="filter-label" for="priority-filter">Priority</label>
                <select class="filter-select" id="priority-filter">
                    <option value="">All Priorities</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="filter-option">
                <label class="filter-label" for="date-filter">Due Date</label>
                <select class="filter-select" id="date-filter">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="tomorrow">Tomorrow</option>
                    <option value="week">Next 7 days</option>
                    <option value="month">Next 30 Days</option>
                </select>
            </div>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-title">Account</div>
        <ul class="sidebar-menu">
            <li <?php echo ($activePage == 'profile') ? 'class="active"' : 'onclick="window.location.href=\'profile-page.php\'"'; ?>><i class="fas fa-user"></i> Profile</li>
            <li <?php echo ($activePage == 'notifications') ? 'class="active"' : 'onclick="window.location.href=\'notifications-page.php\'"'; ?>>
                <i class="fa-solid fa-bell <?php echo ($unreadCount > 0) ? 'bell-animation' : ''; ?>"></i> Notification
            </li>
        </ul>
    </div>
</div>