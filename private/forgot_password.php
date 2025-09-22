<?php
session_start();
$error_message = $_SESSION['error_message'] ?? '';
$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['error_message'], $_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/forgot_password.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <form id="forgotForm" method="POST" action="enter_email.php">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" 
                               value="<?php echo htmlspecialchars($old_email); ?>" required>
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
        <?php if (!empty($error_message)): ?>
            Swal.fire({
                icon: 'error',
                title: '<?php echo $error_message; ?>',
                confirmButtonColor: '#e9b949'
            });
        <?php endif; ?>
    </script>
</body>
</html>
