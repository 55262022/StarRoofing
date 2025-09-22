<?php
session_start();
require_once __DIR__ . '/../database/starroofing_db.php';

$error_type = '';
$error_message = '';
$show_success = false;

if (!isset($_SESSION['reset_email'])) {
    header('Location: enter_email.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $password)) {
        $error_type = 'weak';
        $error_message = 'Password must be at least 6 characters long and include uppercase, lowercase, and a number.';
    } elseif ($password !== $confirm_password) {
        $error_type = 'mismatch';
        $error_message = 'Passwords do not match.';
    } else {
        $email = $_SESSION['reset_email'];
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE accounts SET password = ? WHERE email = ?");
        $stmt->bind_param('ss', $hashed, $email);
        $stmt->execute();

        unset($_SESSION['reset_email']);
        echo "<script>
            sessionStorage.clear();
            window.location.href = '../public/login.php?reset=success';
        </script>";
        exit();
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Container */
        .newpass-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(rgba(26, 54, 93, 0.8), rgba(26, 54, 93, 0.8)), 
                        url('https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1350&q=80') 
                        no-repeat center center/cover;
        }
        .newpass-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .newpass-header {
            background: #1a365d;
            color: white;
            text-align: center;
            padding: 2rem;
        }
        .newpass-header h1 { font-size: 1.8rem; margin-bottom: 0.5rem; }
        .newpass-header p { color: #e9b949; font-size: 0.9rem; }
        .newpass-header i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #e9b949;
            background: rgba(233,185,73,0.2);
            width: 70px; height: 70px;
            line-height: 70px;
            border-radius: 50%;
            display: inline-block;
        }

        /* Body */
        .newpass-body { padding: 2rem; }
        .form-group { margin-bottom: 1.5rem; position: relative; }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1a365d;
            font-size: 0.9rem;
        }
        .form-group .input-icon {
            position: absolute;
            left: 15px; top: 45px;
            color: #1a365d;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 45px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #e9b949;
            box-shadow: 0 0 0 3px rgba(233,185,73,0.2);
        }
        .password-toggle {
            position: absolute;
            right: 15px; top: 40px;
            color: #777;
            cursor: pointer;
        }
        .password-toggle:hover { color: #1a365d; }

        /* Button */
        .newpass-button {
            width: 100%;
            padding: 0.75rem;
            background-color: #1a365d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            transition: background 0.3s;
        }
        .newpass-button:hover {
            background-color: #e9b949;
            color: #1a365d;
        }

        /* Messages */
        .error-message {
            background: #fee;
            border-left: 4px solid #e74c3c;
            color: #c53030;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Strength meter */
        .strength-meter {
            height: 6px;
            background: #ddd;
            border-radius: 3px;
            margin-top: 6px;
            overflow: hidden;
        }
        .strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background 0.3s ease;
        }
        .strength-bar.strength-weak { width: 33%; background: #e74c3c; }
        .strength-bar.strength-moderate { width: 66%; background: #f39c12; }
        .strength-bar.strength-strong { width: 100%; background: #27ae60; }

        .strength-text {
            margin-top: 6px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .strength-text.weak { color: #e74c3c; }
        .strength-text.moderate { color: #f39c12; }
        .strength-text.strong { color: #27ae60; }

        /* Match message */
        .match-message {
            font-size: 0.95em;
            margin-top: 6px;
            display: none;
        }
        .match-message.no-match { color: #e74c3c; display: block; }
        .match-message.match { color: #27ae60; display: block; }

        /* Footer */
        .footer {
            background-color: #1a365d;
            color: #fff;
            padding: 2rem 0 1rem;
            text-align: center;
        }
        .footer-bottom {
            border-top: 1px solid rgba(233,185,73,0.3);
            padding-top: 1.5rem;
        }
        .footer-bottom p { color: #e2e8f0; font-size: 0.9rem; }
    </style>
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
                    <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?= $error_message; ?></div>
                <?php endif; ?>

                <?php if (empty($show_success)): ?>
                <form id="newpassForm" method="POST" action="">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Enter new password" required>
                        <span class="password-toggle" onclick="togglePassword('password', this)"><i class="fas fa-eye"></i></span>
                        <div class="strength-meter" id="strengthMeter">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                        <span class="password-toggle" onclick="togglePassword('confirm_password', this)"><i class="fas fa-eye"></i></span>
                        <div class="match-message" id="matchMessage"></div>
                    </div>

                    <button type="submit" class="newpass-button">Reset Password</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, toggleIcon) {
            const input = document.getElementById(inputId);
            const icon = toggleIcon.querySelector('i');
            input.type = input.type === "password" ? "text" : "password";
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const confirmInput = document.getElementById('confirm_password');
        const matchMessage = document.getElementById('matchMessage');

        // Password strength check
        passwordInput.addEventListener('input', function() {
            const val = passwordInput.value;
            let strength = 0;
            if (val.length >= 6) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[a-z]/.test(val)) strength++;
            if (/\d/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            strengthText.className = 'strength-text';
            if (val.length === 0) {
                strengthBar.className = 'strength-bar';
                strengthBar.style.width = '0';
                strengthText.textContent = '';
            } else if (strength <= 2) {
                strengthBar.className = 'strength-bar strength-weak';
                strengthText.textContent = 'Weak Password';
                strengthText.classList.add('weak');
            } else if (strength === 3 || strength === 4) {
                strengthBar.className = 'strength-bar strength-moderate';
                strengthText.textContent = 'Moderate Password';
                strengthText.classList.add('moderate');
            } else {
                strengthBar.className = 'strength-bar strength-strong';
                strengthText.textContent = 'Strong Password';
                strengthText.classList.add('strong');
            }
            checkMatch();
        });

        // Password match check
        function checkMatch() {
            matchMessage.className = 'match-message';
            if (confirmInput.value.length === 0) {
                matchMessage.textContent = '';
                return;
            }
            if (passwordInput.value === confirmInput.value) {
                matchMessage.textContent = 'Passwords match';
                matchMessage.classList.add('match');
            } else {
                matchMessage.textContent = 'Passwords do not match';
                matchMessage.classList.add('no-match');
            }
        }
        confirmInput.addEventListener('input', checkMatch);

        // SweetAlert validation
        document.getElementById('newpassForm').addEventListener('submit', function(e) {
            const pw = passwordInput.value;
            const cpw = confirmInput.value;
            if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/.test(pw)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Password must be at least 6 characters and include uppercase, lowercase, and a number.',
                    confirmButtonColor: '#e9b949'
                });
            } else if (pw !== cpw) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords do not match.',
                    confirmButtonColor: '#e9b949'
                });
            }
        });

        <?php if ($error_type === 'weak'): ?>
            Swal.fire({ icon: 'warning', title: 'Password must be at least 6 characters and include uppercase, lowercase, and a number.', confirmButtonColor: '#e9b949' });
        <?php elseif ($error_type === 'mismatch'): ?>
            Swal.fire({ icon: 'error', title: 'Passwords do not match.', confirmButtonColor: '#e9b949' });
        <?php endif; ?>
    </script>
</body>
</html>
