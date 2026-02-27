/**
 * Task management functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const addTaskBtn = document.querySelector('.add-task-button');
    const modal = document.getElementById('addTaskModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const modalCancelBtn = document.getElementById('cancelTask');
    let currentTaskCard = null;
    
    // Function to reset form to "add" state
    function resetForm() {
        document.getElementById('addTaskForm').reset();
        document.getElementById('addTaskForm').action = 'add_task.php';
        document.querySelector('.btn-submit').textContent = 'Add Task';
        // Remove task ID if it exists
        const taskIdInput = document.getElementById('taskId');
        if (taskIdInput) taskIdInput.remove();
    }
    
    // Function to open modal
    function openModal() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
    }
    
    // Function to close modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
        resetForm(); // Reset form when closing
    }
    
    // AJAX form submission for add/update task
    const taskForm = document.getElementById('addTaskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent traditional form submission
            
            // Collect form data
            const formData = new FormData(this);
            const formAction = this.action; // Get the form's action URL
            
            // Convert FormData to URL-encoded string
            const urlEncodedData = new URLSearchParams(formData).toString();
            
            // Send AJAX request
            fetch(formAction, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: urlEncodedData
            })
            .then(response => response.json())
            .then(data => {
                // Show alert with the message from server using the shared function
                showAlert(data.message, data.success ? 'success' : 'error');
                
                if (data.success) {
                    // Close the modal
                    closeModal();
                    
                    // Reload tasks without full page refresh
                    // For simplicity, we'll reload the page for now
                    // In a production app, you could implement refreshTasks() instead
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while processing your request.', 'error');
            });
        });
    }
    
    // Open modal when add task button is clicked
    if (addTaskBtn) {
        addTaskBtn.addEventListener('click', function() {
            // Make sure form is in "add" state
            resetForm();
            document.getElementById('addTaskForm').action = 'add_task.php';
            document.querySelector('.btn-submit').textContent = 'Add Task';
            document.querySelector('#addTaskModal .modal-header h2').textContent = 'Add New Task';
            openModal();
        });
    }
    
    // Close modal when X is clicked
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            closeModal();
        });
    }
    
    // Close modal when Cancel button is clicked
    if (modalCancelBtn) {
        modalCancelBtn.addEventListener('click', function() {
            closeModal();
        });
    }
    
    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Set today's date as the minimum date for the due date field
    const dueDateInput = document.getElementById('taskDueDate');
    if (dueDateInput) {
        const today = new Date().toISOString().split('T')[0];
        dueDateInput.setAttribute('min', today);
        
        // Optional: Set default due date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dueDateInput.valueAsDate = tomorrow;
    }
    
    // Helper function to parse date from text (e.g., "Due: Mar 22, 2025" -> "2025-03-22")
    function parseDateFromText(dateText) {
        // Extract just the date part
        const datePart = dateText.replace('Due:', '').trim();
        // Parse the date
        const date = new Date(datePart);
        // Use local date components instead of UTC conversion
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        
        // Format as YYYY-MM-DD for input field
        return `${year}-${month}-${day}`;
    }
    
    // Add click event listeners to all edit buttons
    function setupEditButtons() {
        const editButtons = document.querySelectorAll('.task-action-btn .fa-pen');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                // Prevent the event from bubbling up
                event.stopPropagation();
                
                // Get the task card this button belongs to
                const taskCard = this.closest('.task-card');
                
                // Extract data from the task card
                const taskTitle = taskCard.querySelector('.task-title').textContent;
                const taskDescription = taskCard.querySelector('.task-description').textContent.trim();
                const taskCategory = taskCard.querySelector('.task-category').textContent;
                const taskPriority = taskCard.querySelector('.task-priority').textContent;
                const taskStatus = taskCard.querySelector('.status-badge').textContent;
                
                // Get the due date
                const dueDateText = taskCard.querySelector('.task-due-date').textContent;
                const dueDate = parseDateFromText(dueDateText);
                
                // Get the task ID and reminder status
                const taskId = taskCard.dataset.taskId;
                const reminderEnabled = taskCard.dataset.reminderEnabled === '1';
                
                // Populate the form with task data
                document.getElementById('taskTitle').value = taskTitle;
                document.getElementById('taskDescription').value = taskDescription;
                document.getElementById('taskCategory').value = taskCategory;
                document.getElementById('taskPriority').value = taskPriority;
                document.getElementById('taskStatus').value = taskStatus;
                document.getElementById('taskDueDate').value = dueDate;
                document.getElementById('enableReminder').checked = reminderEnabled;
                
                // Add a hidden input for the task ID
                let taskIdInput = document.getElementById('taskId');
                if (!taskIdInput) {
                    taskIdInput = document.createElement('input');
                    taskIdInput.type = 'hidden';
                    taskIdInput.id = 'taskId';
                    taskIdInput.name = 'task_id';
                    document.getElementById('addTaskForm').appendChild(taskIdInput);
                }
                taskIdInput.value = taskId;
                
                // Change the form submission URL and button text
                const form = document.getElementById('addTaskForm');
                form.action = 'update_task.php';
                document.querySelector('.btn-submit').textContent = 'Update Task';
                
                document.querySelector('#addTaskModal .modal-header h2').textContent = 'Edit Task';
                // Show the modal
                openModal();
            });
        });
    }
    
    // Setup archive buttons
    function setupArchiveButtons() {
        const archiveButtons = document.querySelectorAll('.task-action-btn .fa-archive');
        
        archiveButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                currentTaskCard = this.closest('.task-card');
                const taskId = currentTaskCard.dataset.taskId;
                
                // Use the shared confirm dialog function
                showConfirmDialog('Are you sure you want to archive this task?', function(confirmed) {
                    if (confirmed) {
                        // Send archive request to server
                        fetch('../actions/archive_task.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'task_id=' + taskId
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Show alert with the message from server using the shared function
                            showAlert(data.message, data.success ? 'success' : 'error');
                            
                            if (data.success) {
                                currentTaskCard.style.transition = 'opacity 0.3s ease';
                                currentTaskCard.style.opacity = '0';
                                setTimeout(() => {
                                    currentTaskCard.remove();
                                }, 300);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('An error occurred while archiving the task.', 'error');
                        });
                    }
                });
            });
        });
    }

    // Setup delete buttons
    function setupDeleteButtons() {
        const deleteButtons = document.querySelectorAll('.task-action-btn .fa-trash');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                currentTaskCard = this.closest('.task-card');
                const taskId = currentTaskCard.dataset.taskId;
                
                // Use the shared confirm dialog function
                showConfirmDialog('Are you sure you want to delete this task? This action cannot be undone.', function(confirmed) {
                    if (confirmed) {
                        // Send delete request to server
                        fetch('../actions/delete_task.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'task_id=' + taskId
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Show alert with the message from server
                            showAlert(data.message, data.success ? 'success' : 'error');
                            
                            if (data.success) {
                                currentTaskCard.style.transition = 'opacity 0.3s ease';
                                currentTaskCard.style.opacity = '0';
                                setTimeout(() => {
                                    currentTaskCard.remove();
                                }, 300);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('An error occurred while deleting the task.', 'error');
                        });
                    }
                });
            });
        });
    }
    
    // Initialize the button handlers
    setupEditButtons();
    setupArchiveButtons();
    setupDeleteButtons();
    
    // Optional: Function to refresh tasks without page reload
    // Uncomment and implement if you create the get_tasks.php endpoint
    /*
    function refreshTasks() {
        fetch('get_tasks.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Replace tasks in the UI
                    // Implementation depends on your HTML structure
                }
            })
            .catch(error => {
                console.error('Error refreshing tasks:', error);
            });
    }
    */
});