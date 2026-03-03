/**
 * Login functionality (API version)
 *
 * WHAT THIS DOES:
 * When the user submits the login form, this script:
 * 1. Calls the API to get a JWT token
 * 2. Stores the token in localStorage (for API calls)
 * 3. Submits the form normally to create a PHP session (for page access)
 *
 * WHY BOTH TOKEN AND SESSION?
 * The PHP pages (dashboard, profile, etc.) still check $_SESSION
 * to decide if you can view them. The JWT token is used by JavaScript
 * when making API calls. This dual approach lets both systems coexist.
 */
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form[action*="login.php"]');

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                // Let the normal form handle empty field validation
                this.submit();
                return;
            }

            try {
                // Step 1: Call the API to get JWT token
                const response = await fetch(getBaseUrl() + '/api/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (data.success && data.token) {
                    // Step 2: Store the JWT token
                    saveToken(data.token);
                    console.log('JWT token stored successfully');
                }

                // Step 3: Submit the form normally to create PHP session
                // This handles both success (redirect to dashboard)
                // and failure (show error on login page)
                this.submit();

            } catch (error) {
                console.error('API login error:', error);
                // If API call fails, still submit normally
                // The session-based login will handle it
                this.submit();
            }
        });
    }
});