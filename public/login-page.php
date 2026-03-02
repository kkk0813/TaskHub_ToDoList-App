<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHub - Login</title>
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
                <h1>Welcome to TaskHub</h1>
                <p class="subtitle">Please login to your account or create a new account to continue</p>
            </div>
            
            <form action="../includes/login.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <?php
                // Display error message if any
                if (isset($_SESSION['error'])) {
                    echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']); // Clear the error message
                }
                ?>
                
                <button type="submit" class="btn btn-primary">LOGIN</button>
            </form>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <div class="social-login" onclick="window.location.href='google-login.php'">
                <div class="social-btn">
                    <i class="fa-brands fa-google"></i>
                </div>

            </div>
            
            <button class="btn btn-outline" onclick="window.location.href='register-page.php'">CREATE ACCOUNT</button>
        </div>
    </div>
    
    <footer>
        <p>This business is fictitious and is part of a university course. © 2025 TaskHub</p>
    </footer>
</body>
</html>