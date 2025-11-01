<?php
session_start();
include '../database/starroofing_db.php';

// If user already logged in, redirect based on their role
if (isset($_SESSION['account_id']) || isset($_SESSION['employee_id'])) {
    $returnUrl = $_GET['return_url'] ?? null;

    if (!empty($returnUrl)) {
        header("Location: " . urldecode($returnUrl));
        exit();
    }

    if (isset($_SESSION['role_id'])) {
        if ($_SESSION['role_id'] == 1) {
            header("Location: ../admin/dashboard.php");
        } elseif ($_SESSION['role_id'] == 2) {
            header("Location: homepage.php");
        } elseif ($_SESSION['role_id'] == 3) {
            header("Location: ../employee/dashboard.php");
        }
    } elseif (isset($_SESSION['employee_id'])) {
        header("Location: ../employee/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

$error = '';
$success = '';
$email_value = '';

// Session-based flash messages
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $email_value = $email;
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Query accounts table with role_id and employee data
        $sql = "SELECT a.id, a.email, a.password, a.role_id, a.account_status,
                       up.first_name AS user_first_name, up.last_name AS user_last_name,
                       e.employee_id, e.first_name AS emp_first_name, e.last_name AS emp_last_name,
                       e.department, e.is_archived
                FROM accounts a 
                LEFT JOIN user_profiles up ON a.id = up.account_id 
                LEFT JOIN employees e ON a.id = e.account_id
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
                        $error = "Your account is " . htmlspecialchars($user['account_status']) . ". Please contact the administrator.";
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
                        $_SESSION['role_id'] = $user['role_id'];
                        
                        // Check if this is an employee (role_id = 3)
                        if ($user['role_id'] == 3 && !empty($user['employee_id'])) {
                            // Check if employee is archived
                            if ($user['is_archived'] == 1) {
                                $error = "Your employee account is inactive. Please contact the administrator.";
                                session_destroy();
                            } else {
                                // Employee login
                                $_SESSION['employee_id'] = $user['employee_id'];
                                $_SESSION['first_name'] = $user['emp_first_name'];
                                $_SESSION['last_name'] = $user['emp_last_name'];
                                $_SESSION['department'] = $user['department'];
                                $_SESSION['is_employee'] = true;
                                
                                $_SESSION['success'] = "Log In Successful!";
                                header("Location: ../employee/dashboard.php");
                                exit();
                            }
                        } else {
                            // Regular user (admin or client)
                            $_SESSION['first_name'] = $user['user_first_name'];
                            $_SESSION['last_name'] = $user['user_last_name'];
                            
                            $_SESSION['success'] = "Log In Successful!";

                            // Redirect based on role
                            if ($user['role_id'] == 1) {
                                header("Location: ../admin/dashboard.php");
                            } elseif ($user['role_id'] == 2) {
                                header("Location: homepage.php");
                            } else {
                                header("Location: ../index.php");
                            }
                            exit();
                        }
                    }
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
            
            $stmt->close();
        } else {
            error_log("Prepare failed: " . $conn->error);
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <a href="../index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Home</span>
    </a>
    
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
                    Don't have an account? <a href="register.php">Register Here</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
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

        <?php if (!empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Log In Failed',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonColor: '#3B71CA'
            });
        <?php endif; ?>

        <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Password reset Successful',
                text: 'You can now log in with your new password.',
                timer: 3000,
                confirmButtonColor: '#3B71CA'
            });
        <?php endif; ?>
    </script>
</body>
</html>