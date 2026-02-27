document.addEventListener('DOMContentLoaded', function() {
    function setupPersonalInfoForm() {
        const personalInfoForm = document.getElementById('personalInfoForm');
        
        if (personalInfoForm) {
            personalInfoForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Remove any existing error messages
                clearFormErrors(personalInfoForm);
                
                // Collect form data
                const formData = new FormData(this);
                
                // Add the action parameter manually
                formData.append('update_profile', 'true');
                
                // Convert to URL-encoded string
                const urlEncodedData = new URLSearchParams(formData).toString();
                
                // Send AJAX request
                fetch('../actions/profile-update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: urlEncodedData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update user name in the header and profile card if it changed
                        if (data.newName) {
                            // Update profile name in header
                            document.querySelector('.profile-name').textContent = 'Welcome, ' + data.newName;
                            
                            // Update profile name in profile card
                            const profileNameElement = document.querySelector('.profile-card .profile-name');
                            if (profileNameElement) {
                                profileNameElement.textContent = data.newName;
                            }
                        }
                        
                        // Show success message
                        showAlert(data.message, 'success');
                    } else {
                        // Show error message
                        showAlert(data.message, 'error');
                        
                        // Display field-specific errors if any
                        if (data.errors) {
                            displayFormErrors(personalInfoForm, data.errors);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred. Please try again.', 'error');
                });
            });
        }
    }

    /**
     * Sets up the password form for client-side validation and AJAX submission
     */
    function setupPasswordForm() {
        const passwordForm = document.getElementById('passwordForm');
        
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Clear previous error messages
                clearFormErrors(passwordForm);
                
                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                let hasError = false;
                
                // Client-side validation
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
                
                // Collect form data
                const formData = new FormData(this);
                
                // Convert to URL-encoded string
                const urlEncodedData = new URLSearchParams(formData).toString();
                
                // Send AJAX request
                fetch('../actions/profile-update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: urlEncodedData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showAlert(data.message, 'success');
                        
                        // Reset the form
                        resetForm('passwordForm');
                    } else {
                        // Show error message
                        showAlert(data.message, 'error');
                        
                        // Display field-specific errors if any
                        if (data.errors) {
                            displayFormErrors(passwordForm, data.errors);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred. Please try again.', 'error');
                });
            });
        }
    }

    /**
     * Sets up the notifications form for AJAX submission
     */
    function setupNotificationsForm() {
        const notificationForm = document.getElementById('notificationForm');
        
        if (notificationForm) {
            notificationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Collect form data
                const formData = new FormData(this);
                
                // Convert to URL-encoded string
                const urlEncodedData = new URLSearchParams(formData).toString();
                
                // Send AJAX request
                fetch('../actions/profile-update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: urlEncodedData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showAlert(data.message, 'success');
                    } else {
                        // Show error message
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred. Please try again.', 'error');
                });
            });
        }
    }

    /**
     * Sets up profile image upload functionality
     */
    function setupProfileImageUpload() {
        const changePhotoBtn = document.getElementById('change-photo-btn');
        const profileImageInput = document.getElementById('profileImageInput');
        
        if (changePhotoBtn && profileImageInput) {
            // Trigger file input when change photo button is clicked
            changePhotoBtn.addEventListener('click', function() {
                profileImageInput.click();
            });
            
            // Handle file selection
            profileImageInput.addEventListener('change', function() {
                const fileInput = this;
                if (fileInput.files && fileInput.files[0]) {
                    const formData = new FormData();
                    formData.append('profile_image', fileInput.files[0]);
                    
                    // Show loading state
                    const previewImg = document.getElementById('profile-preview');
                    if (previewImg) {
                        previewImg.style.opacity = '0.5';
                    }
                    
                    fetch('../actions/upload_profile_image.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update all profile images on the page
                            const allProfileImages = document.querySelectorAll('.profile-pic img, #profile-preview');
                            allProfileImages.forEach(img => {
                                img.src = data.image_url + '?t=' + new Date().getTime();
                                img.style.opacity = '1';
                            });
                            
                            showAlert('Profile picture updated successfully', 'success');
                        } else {
                            if (previewImg) {
                                previewImg.style.opacity = '1';
                            }
                            showAlert('Failed to update profile picture: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        if (previewImg) {
                            previewImg.style.opacity = '1';
                        }
                        showAlert('An error occurred during upload', 'error');
                        console.error('Error:', error);
                    });
                }
            });
        }
    }

    /**
     * Display an error message for a specific form field
     * @param {string} fieldId - The ID of the field to attach error to
     * @param {string} message - The error message to display
     */
    function displayError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message client-error-message';
            errorMsg.textContent = message;
            field.parentNode.appendChild(errorMsg);
        }
    }

    /**
     * Display multiple errors on a form
     * @param {HTMLElement} form - The form element
     * @param {Object} errors - Object with field IDs as keys and error messages as values
     */
    function displayFormErrors(form, errors) {
        for (const fieldId in errors) {
            displayError(fieldId, errors[fieldId]);
        }
    }

    /**
     * Clear all error messages from a form
     * @param {HTMLElement} form - The form to clear errors from
     */
    function clearFormErrors(form) {
        const errorMessages = form.querySelectorAll('.error-message, .client-error-message');
        errorMessages.forEach(el => el.remove());
    }

    /**
     * Initialize alerts to auto-dismiss
     */
    function initializeAlerts() {
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
    }

    /**
     * Reset a form to its original state and clear error messages
     * @param {string} formId - The ID of the form to reset
     */
    function resetForm(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            clearFormErrors(form);
        }
    }

    setupPersonalInfoForm();
    setupPasswordForm();
    setupNotificationsForm();
    setupProfileImageUpload();
    initializeAlerts();
    
    // Expose resetForm for use from HTML
    window.resetForm = resetForm;
});