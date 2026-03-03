/**
 * Logout functionality (API version)
 *
 * WHAT CHANGED:
 * Added clearToken() call to remove the JWT from localStorage
 * before redirecting to the logout PHP script.
 * This ensures both the session AND the token are cleaned up.
 */
document.addEventListener('DOMContentLoaded', function() {
    function setupLogoutButton() {
        const logoutButton = document.getElementById('logoutButton');

        if (logoutButton) {
            logoutButton.addEventListener('click', function(event) {
                event.preventDefault();

                showConfirmDialog('Are you sure you want to logout?', function(confirmed) {
                    if (confirmed) {
                        // NEW: Clear the JWT token from localStorage
                        clearToken();
                        // Then redirect to destroy the PHP session as usual
                        window.location.href = '../includes/logout.php';
                    }
                });
            });
        }
    }

    setupLogoutButton();
});