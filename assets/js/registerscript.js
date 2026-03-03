/**
 * Registration functionality (API version)
 *
 * Similar to login: calls the API first, then submits the form
 * normally so the PHP session flow handles the redirect.
 */
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('form[action*="process-register.php"]');

    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const fullname = document.getElementById('fullname').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            try {
                // Call the API to register
                const response = await fetch(getBaseUrl() + '/api/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        fullname,
                        email,
                        password,
                        confirm_password: confirmPassword
                    })
                });

                const data = await response.json();
                console.log('API registration response:', data.message);

            } catch (error) {
                console.error('API registration error:', error);
            }

            // Submit the form normally regardless
            // The PHP flow handles redirect and error display
            this.submit();
        });
    }
});