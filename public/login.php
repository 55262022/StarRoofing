<?php
session_start();
include '../database/starroofing_db.php';

if (isset($_SESSION['account_id'])) {
    header("Location: ../admin/dashboard.php");
    exit();
}

$error = '';
$success = '';
$email_value = '';

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $email_value = $email;
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $sql = "SELECT a.id, a.email, a.password, a.role_id, a.account_status, 
                       up.first_name, up.last_name
                FROM accounts a 
                LEFT JOIN user_profiles up ON a.id = up.account_id 
                WHERE a.email = ?";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Check if account is active
                    if ($user['account_status'] !== 'active') {
                        $error = "Your account is " . $user['account_status'] . ". Please contact administrator.";
                    } else {
                        // Update last login
                        $update_sql = "UPDATE accounts SET last_login = NOW() WHERE id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("i", $user['id']);
                        $update_stmt->execute();
                        $update_stmt->close();
                        
                        // Set session variables
                        $_SESSION['account_id'] = $user['id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        $_SESSION['role_id'] = $user['role_id'];
                        
                    // Redirect to dashboard
                    $_SESSION['success'] = "Login successful!";
                    header("Location: ../admin/dashboard.php");
                    exit();
                    }
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
            
            $stmt->close();
        } else {
            $error = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/login.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-lock"></i>
                <h1>Log In</h1>
                <p>Access your Star Roofing account</p>
            </div>
            
            <div class="login-body">
                <form id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <i class="fas fa-envelope envelope-icon"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" 
                               value="<?php echo htmlspecialchars($email_value); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <i class="fas fa-key password-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                    </div>
                    
                    <div class="remember-forgot">
                        <div class="remember">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="../private/forgot_password.php" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="login-button">Sign In</button>
                </form>
                
                <div class="separator">Or continue with</div>
                
                <div class="social-login">
                    <a href="#" class="social-btn"><i class="fab fa-google"></i></a>
                </div>
                
                <div class="register-link">
                    Don't have an account? <a href="register.php">Register now</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Simple animation for input focus
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            // Check if input has value on page load
            if (input.value !== '') {
                input.parentElement.classList.add('focused');
                if (input.parentElement.classList.contains('password-container')) {
                    input.parentElement.querySelector('.input-icon').parentElement.classList.add('focused');
                }
            }
            
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
                if (this.parentElement.classList.contains('password-container')) {
                    this.parentElement.querySelector('.input-icon').parentElement.classList.add('focused');
                }
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                    if (this.parentElement.classList.contains('password-container')) {
                        this.parentElement.querySelector('.input-icon').parentElement.classList.remove('focused');
                    }
                }
            });
        });
        
        // Form validation with SweetAlert
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (email.trim() === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter your email address',
                    confirmButtonColor: '#3B71CA'
                });
                return;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address',
                    confirmButtonColor: '#3B71CA'
                });
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Password Too Short',
                    text: 'Password must be at least 6 characters long',
                    confirmButtonColor: '#3B71CA'
                });
                return;
            }
        });

        // Display PHP error messages with SweetAlert
        <?php if (!empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: 'Incorrect Email or Password',
                confirmButtonColor: '#3B71CA'
            });
        <?php endif; ?>

        // Display success message if redirected from another page
        <?php if (isset($_GET['reset_test_passwords']) && !empty($error)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonColor: '#3B71CA'
            });
        <?php endif; ?>
    </script>
</body>
</html>