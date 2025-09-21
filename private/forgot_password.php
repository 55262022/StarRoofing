<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/forgot_password.css">
</head>
<body>
    <!-- Forgot Password Content -->
    <div class="forgot-container">
        <div class="forgot-box">
            <div class="forgot-header">
                <i class="fas fa-key"></i>
                <h1>Reset Password</h1>
                <p>Enter your email to receive a reset code</p>
            </div>
            
            <div class="forgot-body">
                <?php if (isset($error_message) && !empty($error_message)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success_message) && !empty($success_message)): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <form id="forgotForm" method="POST" action="enter_email.php">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                    </div>
                    
                    <button type="submit" class="forgot-button">Send Code</button>
                </form>
                
                <div class="back-to-login">
                    Remember your password? <a href="../public/login.php">Back to Login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            
            if (email.trim() === '') {
                e.preventDefault();
                alert('Please enter your email address');
                return;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
        });
        
        // Simple animation for input focus
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>
</body>
</html>