<?php
// This file assumes $task is an associative array with task details
// that's passed to it when included from another file

// Set default values in case any task data is missing
$taskId = isset($task['task_id']) ? $task['task_id'] : '';
$title = isset($task['title']) ? htmlspecialchars($task['title']) : 'Untitled Task';
$category = isset($task['category']) ? htmlspecialchars($task['category']) : '';
$description = isset($task['description']) ? htmlspecialchars($task['description']) : '';
$dueDate = isset($task['due_date']) ? date('M d, Y', strtotime($task['due_date'])) : 'No due date';
$priority = isset($task['priority']) ? strtolower($task['priority']) : 'medium';
$status = isset($task['status']) ? strtolower(str_replace(' ', '', $task['status'])) : 'pending';
$reminderEnabled = isset($task['reminder_enabled']) && $task['reminder_enabled'] == 1;

// Map status to appropriate CSS class
$statusClass = "status-{$status}";
$priorityClass = "priority-{$priority}";
?>

<div class="task-card" data-task-id="<?php echo $taskId; ?>" data-reminder-enabled="<?php echo $reminderEnabled ? '1' : '0'; ?>" title="<?php echo $title?>">
    <div class="task-card-header">
        <div>
            <h3 class="task-title"><?php echo $title; ?></h3>
            <div class="task-category"><?php echo $category; ?></div>
        </div>
        <div class="task-priority <?php echo $priorityClass; ?>"><?php echo ucfirst($priority); ?></div>
    </div>
    <div class="task-description">
        <?php echo $description; ?>
    </div>
    <div class="task-meta">
        <div class="task-due-date">
            <i class="far fa-calendar" style="margin-right: 5px;"></i> Due: <?php echo $dueDate; ?>
            <?php if ($reminderEnabled): ?>
                <span class="reminder-badge" title="Reminder enabled">
                    <i class="fas fa-bell" style="margin-left: 8px; color: var(--primary-color);"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="task-status">
        <div class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></div>
        <div class="task-actions">
            <button class="task-action-btn edit-task" title="Edit task"data-task-id="<?php echo $taskId; ?>"><i class="fas fa-pen"></i></button>
            <?php if ($task['status'] == 'Completed' || $status == 'Completed'): ?>
                <button class="task-action-btn archive-task" title="Archive task" data-task-id="<?php echo $taskId; ?>"><i class="fas fa-archive"></i></button>
            <?php endif; ?>
            <button class="task-action-btn delete-task" title="Delete task" data-task-id="<?php echo $taskId; ?>"><i class="fas fa-trash"></i></button>
        </div>
    </div>
</div>