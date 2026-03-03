/**
 * Notification page functionality (API version)
 *
 * WHAT CHANGED:
 * Before: fetch('../actions/notification-actions.php') with form-encoded data
 * After:  apiRequest('/api/notifications/{id}', 'PUT') for mark as read
 *
 * NOTE: Delete and delete-all still use the old endpoint because our API
 * only has mark-as-read. This is intentional to keep the API simple.
 * In a full production app, you would add DELETE /api/notifications/{id}.
 */
document.addEventListener('DOMContentLoaded', function() {

    // Toggle notification option menu
    const optionBtns = document.querySelectorAll('.notification-option-btn');
    optionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            if (menu) {
                document.querySelectorAll('.notification-option-menu').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });
                menu.classList.toggle('show');
            }
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.notification-option-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    });

    // ============================================================
    // MARK AS READ - NOW USES API
    // ============================================================
    const markReadLinks = document.querySelectorAll('.mark-read');
    markReadLinks.forEach(link => {
        link.addEventListener('click', async function(e) {
            e.preventDefault();
            const notificationId = this.getAttribute('data-id');
            const card = this.closest('.notification-card');

            // USE API: PUT /api/notifications/{id}
            const data = await apiRequest('/api/notifications/' + notificationId, 'PUT');

            if (data.success) {
                card.classList.remove('unread');
                showAlert('Notification marked as read', 'success');
            } else {
                showAlert(data.message, 'error');
            }
        });
    });

    // ============================================================
    // REMOVE NOTIFICATION - still uses old endpoint
    // (API doesn't have DELETE for notifications)
    // ============================================================
    const removeLinks = document.querySelectorAll('.remove');
    removeLinks.forEach(link => {
        link.addEventListener('click', async function(e) {
            e.preventDefault();
            const notificationId = this.getAttribute('data-id');
            const card = this.closest('.notification-card');

            const response = await fetch('../actions/notification-actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete&notification_id=' + notificationId
            });
            const data = await response.json();

            if (data.success) {
                card.remove();
                showAlert('Notification removed', 'success');
                checkEmptyState();
            } else {
                showAlert(data.message, 'error');
            }
        });
    });

    // ============================================================
    // MARK ALL AS READ - NOW USES API (loop through each)
    // ============================================================
    const markAllBtn = document.querySelector('.btn-mark-all-read');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async function() {
            const unreadCards = document.querySelectorAll('.notification-card.unread');

            // Mark each unread notification via the API
            for (const card of unreadCards) {
                const notificationId = card.getAttribute('data-id');
                await apiRequest('/api/notifications/' + notificationId, 'PUT');
                card.classList.remove('unread');
            }

            showAlert('All notifications marked as read', 'success');
        });
    }

    // Clear all - still uses old endpoint
    const clearAllBtn = document.querySelector('.btn-clear-all');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            showConfirmDialog('Are you sure you want to delete all notifications?', async function(confirmed) {
                if (confirmed) {
                    const response = await fetch('../actions/notification-actions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=delete_all'
                    });
                    const data = await response.json();

                    if (data.success) {
                        showEmptyState();
                        showAlert('All notifications cleared', 'success');
                    } else {
                        showAlert(data.message, 'error');
                    }
                }
            });
        });
    }

    // Helper: check if notifications list is empty
    function checkEmptyState() {
        if (document.querySelectorAll('.notification-card').length === 0) {
            showEmptyState();
        }
    }

    function showEmptyState() {
        const container = document.querySelector('.notification-container');
        container.innerHTML = '<div class="empty-state" style="display: flex;">' +
            '<div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>' +
            '<h3 class="empty-state-title">No Notifications</h3>' +
            '<p class="empty-state-message">You don\'t have any notifications at the moment.</p>' +
            '</div>';
    }
});