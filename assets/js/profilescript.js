/**
 * Profile page functionality (API version)
 *
 * WHAT CHANGED:
 * Before: fetch('../actions/profile-update.php') with form-encoded data
 * After:  apiRequest('/api/profile', 'PUT', jsonObject)
 *
 * Before: fetch('../actions/upload_profile_image.php') with FormData
 * After:  apiRequest('/api/profile/picture', 'POST', formData, true)
 */
document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // PERSONAL INFO FORM - NOW USES API
    // ============================================================
    function setupPersonalInfoForm() {
        const personalInfoForm = document.getElementById('personalInfoForm');

        if (personalInfoForm) {
            personalInfoForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                clearFormErrors(personalInfoForm);

                const profileData = {
                    action: 'update_profile',
                    fullname: document.getElementById('fullName').value,
                    email: document.getElementById('email').value,
                    department: document.getElementById('department').value
                };

                // USE API instead of direct PHP call
                const data = await apiRequest('/api/profile', 'PUT', profileData);

                if (data.success) {
                    // Update displayed name in header and profile card
                    if (data.user && data.user.name) {
                        const headerName = document.querySelector('.profile-name');
                        if (headerName) headerName.textContent = 'Welcome, ' + data.user.name;

                        const cardName = document.querySelector('.profile-card .profile-name');
                        if (cardName) cardName.textContent = data.user.name;
                    }
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'error');
                    if (data.errors) {
                        data.errors.forEach(err => showAlert(err, 'error'));
                    }
                }
            });
        }
    }

    // ============================================================
    // PASSWORD FORM - NOW USES API
    // ============================================================
    function setupPasswordForm() {
        const passwordForm = document.getElementById('passwordForm');

        if (passwordForm) {
            passwordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                clearFormErrors(passwordForm);

                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                let hasError = false;

                // Client-side validation (same as before)
                if (!currentPassword) {
                    displayError('currentPassword', 'Current password is required.');
                    hasError = true;
                }
                if (!newPassword) {
                    displayError('newPassword', 'New password is required.');
                    hasError = true;
                } else if (newPassword.length < 6) {
                    displayError('newPassword', 'Password must be at least 6 characters long.');
                    hasError = true;
                }
                if (newPassword !== confirmPassword) {
                    displayError('confirmPassword', 'Passwords do not match.');
                    hasError = true;
                }

                if (hasError) {
                    showAlert('Please fix the errors in the form.', 'error');
                    return;
                }

                // USE API instead of direct PHP call
                const data = await apiRequest('/api/profile', 'PUT', {
                    action: 'update_password',
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                });

                if (data.success) {
                    showAlert(data.message, 'success');
                    resetForm('passwordForm');
                } else {
                    showAlert(data.message, 'error');
                }
            });
        }
    }

    // ============================================================
    // PROFILE IMAGE UPLOAD - NOW USES API
    // ============================================================
    function setupProfileImageUpload() {
        const changePhotoBtn = document.getElementById('change-photo-btn');
        const profileImageInput = document.getElementById('profileImageInput');

        if (changePhotoBtn && profileImageInput) {
            changePhotoBtn.addEventListener('click', function() {
                profileImageInput.click();
            });

            profileImageInput.addEventListener('change', async function() {
                if (this.files && this.files[0]) {
                    const previewImg = document.getElementById('profile-preview');
                    if (previewImg) previewImg.style.opacity = '0.5';

                    // Build FormData for file upload
                    const formData = new FormData();
                    formData.append('profile_image', this.files[0]);

                    // USE API - note the 4th parameter (true) tells apiRequest
                    // this is a file upload, not JSON
                    const data = await apiRequest('/api/profile/picture', 'POST', formData, true);

                    if (data.success) {
                        const allProfileImages = document.querySelectorAll('.profile-pic img, #profile-preview');
                        allProfileImages.forEach(img => {
                            img.src = data.image_url + '?t=' + new Date().getTime();
                            img.style.opacity = '1';
                        });
                        showAlert('Profile picture updated successfully', 'success');
                    } else {
                        if (previewImg) previewImg.style.opacity = '1';
                        showAlert('Failed to update profile picture: ' + data.message, 'error');
                    }
                }
            });
        }
    }

    // ============================================================
    // HELPER FUNCTIONS (unchanged from original)
    // ============================================================
    function displayError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message client-error-message';
            errorMsg.textContent = message;
            field.parentNode.appendChild(errorMsg);
        }
    }

    function clearFormErrors(form) {
        const errorMessages = form.querySelectorAll('.error-message, .client-error-message');
        errorMessages.forEach(el => el.remove());
    }

    function resetForm(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            clearFormErrors(form);
        }
    }

    function initializeAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('fade-out');
                setTimeout(() => {
                    if (alert.parentNode) alert.parentNode.removeChild(alert);
                }, 500);
            }, 3000);
        });
    }

    setupPersonalInfoForm();
    setupPasswordForm();
    setupProfileImageUpload();
    initializeAlerts();

    window.resetForm = resetForm;
});