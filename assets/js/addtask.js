/**
 * Task management functionality (API version)
 *
 * WHAT CHANGED FROM THE ORIGINAL:
 * Before: fetch('add_task.php') with form-encoded data
 * After:  apiRequest('/api/tasks', 'POST', jsonObject)
 */
document.addEventListener('DOMContentLoaded', function() {
    const addTaskBtn = document.querySelector('.add-task-button');
    const modal = document.getElementById('addTaskModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const modalCancelBtn = document.getElementById('cancelTask');
    let currentTaskCard = null;

    function resetForm() {
        document.getElementById('addTaskForm').reset();
        document.querySelector('.btn-submit').textContent = 'Add Task';
        const taskIdInput = document.getElementById('taskId');
        if (taskIdInput) taskIdInput.remove();
        document.getElementById('addTaskForm').removeAttribute('data-mode');
        document.getElementById('addTaskForm').removeAttribute('data-task-id');
    }

    function openModal() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetForm();
    }

    // FORM SUBMISSION - NOW USES API
    const taskForm = document.getElementById('addTaskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const taskData = {
                title: document.getElementById('taskTitle').value,
                description: document.getElementById('taskDescription').value,
                category: document.getElementById('taskCategory').value,
                due_date: document.getElementById('taskDueDate').value,
                priority: document.getElementById('taskPriority').value,
                status: document.getElementById('taskStatus').value,
                reminder_enabled: document.getElementById('enableReminder').checked
            };

            const mode = this.getAttribute('data-mode');
            const taskId = this.getAttribute('data-task-id');

            let data;
            if (mode === 'edit' && taskId) {
                data = await apiRequest('/api/tasks/' + taskId, 'PUT', taskData);
            } else {
                data = await apiRequest('/api/tasks', 'POST', taskData);
            }

            showAlert(data.message, data.success ? 'success' : 'error');

            if (data.success) {
                closeModal();
                setTimeout(() => { location.reload(); }, 2000);
            }
        });
    }

    if (addTaskBtn) {
        addTaskBtn.addEventListener('click', function() {
            resetForm();
            document.querySelector('.btn-submit').textContent = 'Add Task';
            document.querySelector('#addTaskModal .modal-header h2').textContent = 'Add New Task';
            openModal();
        });
    }
    if (closeModalBtn) { closeModalBtn.addEventListener('click', closeModal); }
    if (modalCancelBtn) { modalCancelBtn.addEventListener('click', closeModal); }
    window.addEventListener('click', function(event) {
        if (event.target === modal) closeModal();
    });

    const dueDateInput = document.getElementById('taskDueDate');
    if (dueDateInput) {
        const today = new Date().toISOString().split('T')[0];
        dueDateInput.setAttribute('min', today);
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dueDateInput.valueAsDate = tomorrow;
    }

    // EDIT BUTTONS - FETCHES TASK DATA FROM API
    function setupEditButtons() {
        const editButtons = document.querySelectorAll('.task-action-btn .fa-pen');
        editButtons.forEach(button => {
            button.addEventListener('click', async function(event) {
                event.stopPropagation();
                const taskCard = this.closest('.task-card');
                const taskId = taskCard.dataset.taskId;

                const data = await apiRequest('/api/tasks/' + taskId, 'GET');
                if (!data.success) {
                    showAlert('Could not load task details', 'error');
                    return;
                }
                const task = data.task;

                document.getElementById('taskTitle').value = task.title;
                document.getElementById('taskDescription').value = task.description || '';
                document.getElementById('taskCategory').value = task.category;
                document.getElementById('taskPriority').value = task.priority;
                document.getElementById('taskStatus').value = task.status;
                document.getElementById('taskDueDate').value = task.due_date;
                document.getElementById('enableReminder').checked = task.reminder_enabled == 1;

                const form = document.getElementById('addTaskForm');
                form.setAttribute('data-mode', 'edit');
                form.setAttribute('data-task-id', taskId);
                document.querySelector('.btn-submit').textContent = 'Update Task';
                document.querySelector('#addTaskModal .modal-header h2').textContent = 'Edit Task';
                openModal();
            });
        });
    }

    // ARCHIVE BUTTONS - still uses old endpoint (API has no archive action)
    function setupArchiveButtons() {
        const archiveButtons = document.querySelectorAll('.task-action-btn .fa-archive');
        archiveButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                currentTaskCard = this.closest('.task-card');
                const taskId = currentTaskCard.dataset.taskId;

                showConfirmDialog('Are you sure you want to archive this task?', async function(confirmed) {
                    if (confirmed) {
                        const response = await fetch('../actions/archive_task.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'task_id=' + taskId
                        });
                        const data = await response.json();
                        showAlert(data.message, data.success ? 'success' : 'error');
                        if (data.success) {
                            currentTaskCard.style.transition = 'opacity 0.3s ease';
                            currentTaskCard.style.opacity = '0';
                            setTimeout(() => { currentTaskCard.remove(); }, 300);
                        }
                    }
                });
            });
        });
    }

    // DELETE BUTTONS - NOW USES API
    function setupDeleteButtons() {
        const deleteButtons = document.querySelectorAll('.task-action-btn .fa-trash');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                currentTaskCard = this.closest('.task-card');
                const taskId = currentTaskCard.dataset.taskId;

                showConfirmDialog('Are you sure you want to delete this task? This action cannot be undone.', async function(confirmed) {
                    if (confirmed) {
                        const data = await apiRequest('/api/tasks/' + taskId, 'DELETE');
                        showAlert(data.message, data.success ? 'success' : 'error');
                        if (data.success) {
                            currentTaskCard.style.transition = 'opacity 0.3s ease';
                            currentTaskCard.style.opacity = '0';
                            setTimeout(() => { currentTaskCard.remove(); }, 300);
                        }
                    }
                });
            });
        });
    }

    setupEditButtons();
    setupArchiveButtons();
    setupDeleteButtons();
});