/**
 * TaskHub UI Utilities
 * Contains shared UI functions for alerts, dialogs, and form utilities
 */

/**
 * Creates and displays an alert message
 * @param {string} message - The message to display
 * @param {string} type - The type of alert ('success' or 'error')
 */
function showAlert(message, type) {
    // Remove any existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    // Insert after content header if it exists, otherwise at the top of the body
    const contentHeader = document.querySelector('.content-header');
    if (contentHeader) {
        contentHeader.insertAdjacentElement('afterend', alert);
    } else {
        document.body.insertBefore(alert, document.body.firstChild);
    }
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alert.classList.add('fade-out');
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 500);
    }, 3000);
}

/**
 * Shows a custom confirmation dialog
 * @param {string} message - The confirmation message to display
 * @param {function} callback - Function to call with the result (true/false)
 */
function showConfirmDialog(message, callback) {
    const customDialog = document.getElementById('customConfirmDialog');
    const confirmBtn = document.getElementById('confirmBtn');
    const confirmCancelBtn = document.getElementById('cancelBtn');
    const closeConfirm = document.getElementById('closeConfirm');
    const confirmMessage = document.getElementById('confirmMessage');
    
    confirmMessage.textContent = message;
    customDialog.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    const handleConfirm = function() {
        customDialog.style.display = 'none';
        document.body.style.overflow = 'auto';
        callback(true);
        confirmBtn.removeEventListener('click', handleConfirm);
        confirmCancelBtn.removeEventListener('click', handleCancel);
        closeConfirm.removeEventListener('click', handleCancel);
    };
    
    const handleCancel = function() {
        customDialog.style.display = 'none';
        document.body.style.overflow = 'auto';
        callback(false);
        confirmBtn.removeEventListener('click', handleConfirm);
        confirmCancelBtn.removeEventListener('click', handleCancel);
        closeConfirm.removeEventListener('click', handleCancel);
    };
    
    confirmBtn.addEventListener('click', handleConfirm);
    confirmCancelBtn.addEventListener('click', handleCancel);
    closeConfirm.addEventListener('click', handleCancel);
    
    // Close when clicking outside the modal
    window.addEventListener('click', function modalOutsideClick(event) {
        if (event.target === customDialog) {
            handleCancel();
            window.removeEventListener('click', modalOutsideClick);
        }
    });
}

/**
 * Resets a form to its original state
 * @param {string} formId - The ID of the form to reset
 */
function resetForm(formId) {
    document.getElementById(formId).reset();
}

// Initialize alerts (auto-hide existing alerts)
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, 3000);
    });
});