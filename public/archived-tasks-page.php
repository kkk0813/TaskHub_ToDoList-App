<?php
// Include authentication check
require_once '../includes/auth-check.php';
// Get user data from database
include '../includes/PDOconn.php';
try {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? AND archived = '1' ORDER BY due_date ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Handle database error
    $tasks = [];
    $_SESSION['error'] = "Could not load tasks: " . $e->getMessage();
}

$activePage = 'archivedtasks';
require_once '../includes/user-functions.php';
$userData = getUserData($pdo, $_SESSION['user_id']);
$userName = $userData['NAME'];
$profilePicture = $userData['profile_picture'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHub - Archived Tasks</title>
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
                <h1 class="content-title">Archived Tasks</h1>
                <button class="add-task-button"><i class="fas fa-plus"></i>Add Task</button>
            </div>

            <?php if (count($tasks) > 0): ?>
                <div class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <?php include '../components/task-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="empty-state-title">No tasks</h3>
                    <p class="empty-state-message">You don't have any archived tasks at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>This application is fictitious and is part of a university course. © 2025 TaskHub</footer>

    <!-- Add task modal-->
    <?php include '../components/task-modal.php'; ?>
    <?php include '../components/confirmation-dialog.php'; ?>

    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/addtask.js"></script>
    <script src="../assets/js/filters.js"></script>
    <script src="../assets/js/logoutscript.js"></script>
</body>
</html>