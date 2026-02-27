<?php
session_start();
// Get any saved form data
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
// Get any error messages
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHub - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/loginstyle.css">
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="logo-container">
                <div class="logo">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" fill="white"/>
                    </svg>
                </div>
                <h1>Create New Account</h1>
                <p class="subtitle">Please fill in your details to create your account</p>
            </div>

            <?php if (isset($errors['general'])): ?>
                <div class="error-message general-error"><?php echo $errors['general']; ?></div>
            <?php endif; ?>
            
            <form action="../actions/process-register.php" method="post">
            <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" 
                           value="<?php echo isset($form_data['fullname']) ? htmlspecialchars($form_data['fullname']) : ''; ?>"
                           placeholder="Enter your full name" required>
                    <?php if (isset($errors['fullname'])): ?>
                        <div class="error-message"><?php echo $errors['fullname']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>"
                           placeholder="Enter your email" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="error-message"><?php echo $errors['confirm_password']; ?></div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">CREATE ACCOUNT</button>
            </form>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <div class="social-login">
                <div class="social-btn" onclick="window.location.href='google-login.php'">
                    <i class="fa-brands fa-google"></i>
                </div>
            </div>
            
            <div class="login-link">
                Already have an account? <a href="login-page.php">Login</a>
            </div>
        </div>
    </div>
    
    <footer>
        <p>This business is fictitious and is part of a university course. © 2025 TaskHub</p>
    </footer>

    <?php
    // Clear the session data after displaying
    unset($_SESSION['errors']);
    unset($_SESSION['form_data']);
    ?>
</body>
</html>