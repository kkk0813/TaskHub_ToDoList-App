/**
 * View Task functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addTaskModal');
    const closeModalBtn = document.querySelector('.close-modal');
    
    // Function to open modal in view-only mode
    function openViewModal() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
    }
    
    // Function to close modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
        
        // Re-enable form fields after closing
        const formElements = document.getElementById('addTaskForm').elements;
        for (let i = 0; i < formElements.length; i++) {
            formElements[i].removeAttribute('disabled');
        }
        
        // Show the submit button again
        document.querySelector('.btn-submit').style.display = 'block';
    }
    
    // Add click event listeners to all "View Task" links in notifications
    const viewTaskLinks = document.querySelectorAll('.notification-link');
    
    viewTaskLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            
            // Get the task ID from the link
            const taskId = this.getAttribute('href').split('=')[1];
            
            // Fetch task details
            fetch('../actions/get_task.php?id=' + taskId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const task = data.task;
                        
                        // Populate the form with task data
                        document.getElementById('taskTitle').value = task.title;
                        document.getElementById('taskDescription').value = task.description;
                        document.getElementById('taskCategory').value = task.category;
                        document.getElementById('taskPriority').value = task.priority;
                        document.getElementById('taskStatus').value = task.status;
                        document.getElementById('taskDueDate').value = task.due_date;
                        
                        if (document.getElementById('enableReminder')) {
                            document.getElementById('enableReminder').checked = task.reminder_enabled == 1;
                        }
                        
                        // Set form to view-only mode
                        const formElements = document.getElementById('addTaskForm').elements;
                        for (let i = 0; i < formElements.length; i++) {
                            formElements[i].setAttribute('disabled', 'disabled');
                        }
                        
                        // Hide the submit button
                        document.querySelector('.btn-cancel').style.display = 'none';
                        document.querySelector('.btn-submit').style.display = 'none';
                        
                        // Change the modal title
                        document.querySelector('#addTaskModal .modal-header h2').textContent = 'View Task';
                        
                        // Show the modal
                        openViewModal();
                    } else {
                        showAlert('Could not load task details', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while loading task details', 'error');
                });
        });
    });
    
    // Close modal when X is clicked
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            closeModal();
        });
    }
    
    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
});