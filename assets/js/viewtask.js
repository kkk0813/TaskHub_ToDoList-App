/**
 * View Task functionality (API version)
 *
 * WHAT CHANGED:
 * Before: fetch('../actions/get_task.php?id=' + taskId)
 * After:  apiRequest('/api/tasks/' + taskId, 'GET')
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addTaskModal');
    const closeModalBtn = document.querySelector('.close-modal');

    function openViewModal() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';

        const formElements = document.getElementById('addTaskForm').elements;
        for (let i = 0; i < formElements.length; i++) {
            formElements[i].removeAttribute('disabled');
        }

        document.querySelector('.btn-submit').style.display = 'block';
        document.querySelector('.btn-cancel').style.display = 'block';
    }

    const viewTaskLinks = document.querySelectorAll('.notification-link');

    viewTaskLinks.forEach(link => {
        link.addEventListener('click', async function(event) {
            event.preventDefault();

            const taskId = this.getAttribute('href').split('=')[1];

            // USE API instead of direct PHP call
            const data = await apiRequest('/api/tasks/' + taskId, 'GET');

            if (data.success) {
                const task = data.task;

                document.getElementById('taskTitle').value = task.title;
                document.getElementById('taskDescription').value = task.description || '';
                document.getElementById('taskCategory').value = task.category;
                document.getElementById('taskPriority').value = task.priority;
                document.getElementById('taskStatus').value = task.status;
                document.getElementById('taskDueDate').value = task.due_date;

                if (document.getElementById('enableReminder')) {
                    document.getElementById('enableReminder').checked = task.reminder_enabled == 1;
                }

                const formElements = document.getElementById('addTaskForm').elements;
                for (let i = 0; i < formElements.length; i++) {
                    formElements[i].setAttribute('disabled', 'disabled');
                }

                document.querySelector('.btn-cancel').style.display = 'none';
                document.querySelector('.btn-submit').style.display = 'none';
                document.querySelector('#addTaskModal .modal-header h2').textContent = 'View Task';

                openViewModal();
            } else {
                showAlert('Could not load task details', 'error');
            }
        });
    });

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    window.addEventListener('click', function(event) {
        if (event.target === modal) closeModal();
    });
});