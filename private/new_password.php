<?php
session_start();
include '../database/starroofing_db.php'; 

if (!isset($_SESSION['reset_email'])) {
    header('Location: enter_email.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['reset_email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $error_message = 'Password must be at least 8 characters long and contain both letters and numbers.';
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("UPDATE accounts SET password = ? WHERE email = ?");
        $stmt->bind_param('ss', $hashed_password, $email);

        if ($stmt->execute()) {
            // Delete all reset records for this email
            $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $del->bind_param('s', $email);
            $del->execute();

            session_unset();
            session_destroy();
            $success_message = 'Password reset successfully. You can now login with your new password.';
        } else {
            $error_message = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/new_password.css">
</head>
<body>
    <div class="newpass-container">
        <div class="newpass-box">
            <div class="newpass-header">
                <i class="fas fa-lock"></i>
                <h1>Set New Password</h1>
                <p>Create a strong new password for your account</p>
            </div>
            
            <div class="newpass-body">
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="success-message"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
                    <script>
                        setTimeout(() => { window.location.href = '../public/login.php'; }, 3000);
                    </script>
                <?php endif; ?>

                <?php if (empty($success_message)): ?>
                <form id="newpassForm" method="POST" action="">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Enter new password" required>
                        <span class="password-toggle" onclick="togglePassword('password', this)"><i class="fas fa-eye"></i></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                        <span class="password-toggle" onclick="togglePassword('confirm_password', this)"><i class="fas fa-eye"></i></span>
                    </div>
                    
                    <button type="submit" class="newpass-button">Reset Password</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-bottom">
                <p>&copy; 2025 Star Roofing & Construction. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function togglePassword(inputId, toggleIcon) {
            const input = document.getElementById(inputId);
            const icon = toggleIcon.querySelector('i');
            input.type = input.type === "password" ? "text" : "password";
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>
</body>
</html>
