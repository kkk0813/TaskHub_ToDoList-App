<?php
// Include authentication check
require_once '../includes/auth-check.php';
// Get user data from database
include '../includes/PDOconn.php';
// Include user functions
require_once '../includes/user-functions.php';
$activePage = 'profile';

// Get user data
$userData = getUserData($pdo, $_SESSION['user_id']);
$userName = $userData['NAME'];
$userEmail = $userData['email'];
$userDepartment = $userData['department'];
$profilePicture = $userData['profile_picture'];
$pendingCount = $userData['pending_count'];
$completedCount = $userData['completed_count'];

// Get any field-specific errors
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHub - User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboardstyle.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <div class="logo"><i class="fas fa-check" style="color: white;"></i></div>
            <div class="logo-text">TaskHub</div>
        </div>
        <div class="user-menu">
            <div class="profile">
                <div class="profile-pic" style="display: flex; padding: 3px;">
                    <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="profile">
                </div>
                <div class="profile-name">Welcome, <?php echo htmlspecialchars($userName); ?></div>
            </div>
            <div class="logout-btn" id="logoutButton">
                <i class="fas fa-sign-out-alt"></i> Logout
            </div>
        </div>
    </div>

    <div class="main-container">
        <?php include '../components/sidebar.php'; ?>

        <div class="content">
            <div class="content-header">
                <h1 class="content-title">User Profile</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="profile-section">
                <!-- Profile Info Card -->
                <div class="profile-card">
                    <div class="profile-image">
                        <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile picture" id="profile-preview">
                        <div class="change-photo" id="change-photo-btn">Change Photo</div>
                    </div>
                    <h2 class="profile-name"><?php echo htmlspecialchars($userName); ?></h2>
                    <p class="profile-email"><?php echo htmlspecialchars($userEmail); ?></p>
                    
                    <div class="task-summary">
                        <div class="task-stat">
                            <div class="stat-number"><?php echo $pendingCount; ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                        <div class="task-stat">
                            <div class="stat-number"><?php echo $completedCount; ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                    </div>
                    
                    <button class="btn-primary" onclick="window.location.href='dashboard-page.php'">
                        <i class="fas fa-tasks"></i> View My Tasks
                    </button>
                </div>
                
                <!-- Profile Settings -->
                <div class="settings-card" style="margin-right: 30px;">
                    <h2 class="settings-title">Profile Settings</h2>
                    
                    <div class="settings-group">
                        <h3 class="settings-group-title">Personal Information</h3>
                        <form id="personalInfoForm" method="POST" action="../actions/profile-update.php">
                            <div class="form-group">
                                <label class="form-label" for="fullName">Full Name</label>
                                <input type="text" class="form-input" id="fullName" name="fullName" value="<?php echo htmlspecialchars($userName); ?>">
                                <?php if (isset($errors['fullName'])): ?>
                                    <div class="error-message"><?php echo $errors['fullName']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Email Address</label>
                                <input type="email" class="form-input" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="error-message"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="department">Department</label>
                                <input type="text" class="form-input" id="department" name="department" value="<?php echo htmlspecialchars($userDepartment); ?>">
                                <?php if (isset($errors['department'])): ?>
                                    <div class="error-message"><?php echo $errors['department']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="resetForm('personalInfoForm')">Cancel</button>
                                <button type="submit" class="btn-submit" name="update_profile">Save Changes</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="settings-group">
                        <h3 class="settings-group-title">Change Password</h3>
                        <form id="passwordForm" method="POST" action="../actions/profile-update.php">
                            <div class="form-group">
                                <label class="form-label" for="currentPassword">Current Password</label>
                                <input type="password" class="form-input" id="currentPassword" name="currentPassword" placeholder="Enter current password" required>
                                <?php if (isset($errors['currentPassword'])): ?>
                                    <div class="error-message"><?php echo $errors['currentPassword']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="newPassword">New Password</label>
                                <input type="password" class="form-input" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                                <?php if (isset($errors['newPassword'])): ?>
                                    <div class="error-message"><?php echo $errors['newPassword']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="confirmPassword">Confirm New Password</label>
                                <input type="password" class="form-input" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required>
                                <?php if (isset($errors['confirmPassword'])): ?>
                                    <div class="error-message"><?php echo $errors['confirmPassword']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="resetForm('passwordForm')">Cancel</button>
                                <button type="submit" class="btn-submit" name="update_password">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>This application is fictitious and is part of a university course. © 2025 TaskHub</footer>

    <!-- Hidden form for image upload -->
    <form id="profileImageForm" style="display: none;">
        <input type="file" id="profileImageInput" name="profile_image" accept="image/*">
    </form>

    <?php include '../components/confirmation-dialog.php'; ?>
    <script src="../assets/js/alerts.js"></script>
    <script src="../assets/js/logoutscript.js"></script>
    <script>
    // Client-side password validation
    document.addEventListener('DOMContentLoaded', function() {
        const passwordForm = document.getElementById('passwordForm');
        
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                let hasError = false;
                
                // Reset any previous error messages
                const existingErrorMessages = document.querySelectorAll('.client-error-message');
                existingErrorMessages.forEach(el => el.remove());
                
                // Validate password length
                if (newPassword.length < 6) {
                    e.preventDefault(); // Stop form submission
                    hasError = true;
                    
                    // Display error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message client-error-message';
                    errorMsg.textContent = 'Password must be at least 6 characters long.';
                    
                    // Insert after the password field
                    const passwordField = document.getElementById('newPassword');
                    passwordField.parentNode.appendChild(errorMsg);
                }
                
                // Check if passwords match
                if (newPassword !== confirmPassword) {
                    e.preventDefault(); // Stop form submission
                    hasError = true;
                    
                    // Display error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message client-error-message';
                    errorMsg.textContent = 'Passwords do not match.';
                    
                    // Insert after the confirm password field
                    const confirmField = document.getElementById('confirmPassword');
                    confirmField.parentNode.appendChild(errorMsg);
                }
                
                if (hasError) {
                    // Show a general error message
                    showAlert('Please fix the errors in the form.', 'error');
                }
            });
        }

        // Get DOM elements
        const changePhotoBtn = document.getElementById('change-photo-btn');
        const profileImageInput = document.getElementById('profileImageInput');
        const profileImagePreview = document.getElementById('profile-preview');
        
        // When "Change Photo" button is clicked, trigger the file input
        if (changePhotoBtn && profileImageInput) {
            changePhotoBtn.addEventListener('click', function() {
                profileImageInput.click();
            });
        }
        
        // When a file is selected, upload it
        if (profileImageInput) {
            profileImageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    // Show a loading indicator or message
                    changePhotoBtn.textContent = 'Uploading...';
                    
                    // Create FormData object and append the file
                    const formData = new FormData();
                    formData.append('profile_image', this.files[0]);
                    
                    // Send AJAX request to upload the file
                    fetch('../actions/upload_profile_image.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the profile image on the page
                            profileImagePreview.src = data.image_url;
                            
                            // Also update the small profile image in the header
                            const headerProfileImg = document.querySelector('.profile-pic img');
                            if (headerProfileImg) {
                                headerProfileImg.src = data.image_url;
                            }
                            
                            // Show success message
                            showAlert('Profile picture updated successfully', 'success');
                        } else {
                            // Show error message
                            showAlert(data.message || 'Failed to update profile picture', 'error');
                        }
                        
                        // Reset the button text
                        changePhotoBtn.textContent = 'Change Photo';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('An error occurred while uploading the image', 'error');
                        changePhotoBtn.textContent = 'Change Photo';
                    });
                }
            });
        }
    });
    </script>
    
    <?php
    // Clear any field-specific errors after displaying
    unset($_SESSION['errors']);
    ?>
</body>
</html>