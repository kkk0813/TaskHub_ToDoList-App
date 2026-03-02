document.addEventListener('DOMContentLoaded', function() {
    // Set up logout confirmation
    function setupLogoutButton() {
        const logoutButton = document.getElementById('logoutButton');
        
        if (logoutButton) {
            logoutButton.addEventListener('click', function(event) {
                event.preventDefault();
                
                showConfirmDialog('Are you sure you want to logout?', function(confirmed) {
                    if (confirmed) {
                        window.location.href = '../includes/logout.php';
                    }
                });
            });
        }
    }
    
    // Call the function to set up the logout button
    setupLogoutButton();
});