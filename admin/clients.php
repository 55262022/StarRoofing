<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="../css/register.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
              /* Base styles and reset */
      * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
      }
      body {
          font-family: 'Montserrat', sans-serif;
          line-height: 1.6;
          color: #333;
          background-color: #f5f7f9;
          min-height: 100vh;
          display: flex;
          flex-direction: column;
      }
      .dashboard-container {
          display: flex;
          min-height: 100vh;
      }
      .main-content {
          flex: 1;
          display: flex;
          flex-direction: column;
      }
      .dashboard-content {
          flex: 1;
          padding: 1.5rem;
          overflow-y: auto;
      }
      .page-title {
          font-size: 1.8rem;
          color: #1a365d;
          margin-bottom: 1.5rem;
          font-weight: 700;
      }
      .container {
          padding: 20px;
      }

      /* Modal wrapper */
.modal {
    display: none; 
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    overflow-y: auto;
    padding-top: 40px;
}

/* Modal content box */
.modal-content {
    background: transparent;
    margin: auto;
    width: 90%;
    max-width: 750px;
    border-radius: 10px;
    position: relative;
}

/* Close button */
.close-btn {
    position: absolute;
    top: 10px; right: 15px;
    font-size: 28px;
    font-weight: bold;
    color: white;
    cursor: pointer;
    z-index: 1100;
}
.close-btn:hover {
    color: #e9b949;
}


        /* Registration Page Specific Styles */
        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(rgba(26, 54, 93, 0.8), rgba(26, 54, 93, 0.8)), url('https://images.unsplash.com/photo-1503387762-592deb58ef4e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
        }

        .register-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 700px;
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .register-header {
            background: #1a365d;
            color: white;
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        .register-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: #e9b949;
            font-size: 0.9rem;
        }

        .register-header i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #e9b949;
            background: rgba(233, 185, 73, 0.2);
            width: 70px;
            height: 70px;
            line-height: 70px;
            border-radius: 50%;
            display: inline-block;
            text-align: center;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            position: relative;
        }

        .progress-step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9b949;
            color: #1a365d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            z-index: 2;
        }

        .progress-step.active {
            background: #e9b949;
            color: #1a365d;
        }

        .progress-step.inactive {
            background: #cbd5e0;
            color: #718096;
        }

        .progress-bar::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #cbd5e0;
            z-index: 1;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            height: 2px;
            background: #e9b949;
            z-index: 1;
            width: 50%;
            transition: width 0.5s ease;
        }

        .step-2-active::after {
            width: 100%;
        }

        .step1-label {
            position: absolute;
            top: 40px;
            font-size: 0.7rem;
            color: white;
            width: 80px;
            text-align: center;
            left: -25px;
        }

        .step2-label {
            position: absolute;
            top: 40px;
            font-size: 0.7rem;
            color: white;
            width: 80px;
            text-align: center;
        }

        .register-body {
            padding: 2rem;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            flex: 1;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1a365d;
            font-size: 0.9rem;
        }

        .form-group .input-icon {
            position: absolute;
            left: 15px;
            top: 45px;
            color: #1a365d;
        }

        .form-group .envelope-icon {
            position: absolute;
            left: 15px;
            top: 45px;
            color: #1a365d;
        }

        .form-group .password-icon {
            position: absolute;
            left: 15px;
            top: 45px;
            color: #1a365d;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 45px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #e9b949;
            box-shadow: 0 0 0 3px rgba(233, 185, 73, 0.2);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .address-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .nav-button {
            padding: 0.75rem 1.5rem;
            background-color: #1a365d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-button:hover {
            background-color: #e9b949;
            color: #1a365d;
        }

        .nav-button.prev {
            background-color: #cbd5e0;
            color: #4a5568;
        }

        .nav-button.prev:hover {
            background-color: #a0aec0;
        }

        .password-strength {
            height: 5px;
            background: #e2e8f0;
            border-radius: 3px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .password-weak .password-strength-bar {
            background: #e53e3e;
            width: 33%;
        }

        .password-medium .password-strength-bar {
            background: #dd6b20;
            width: 66%;
        }

        .password-strong .password-strength-bar {
            background: #38a169;
            width: 100%;
        }

        .password-feedback {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            color: #718096;
        }

        .terms {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin: 1.5rem 0;
        }

        .terms input {
            margin-top: 0.2rem;
        }

        .terms label {
            font-size: 0.9rem;
            color: #4a5568;
        }

        .terms a {
            color: #1a365d;
            text-decoration: none;
            font-weight: 600;
        }

        .terms a:hover {
            text-decoration: underline;
            color: #e9b949;
        }

        /* Footer Styles */
        .footer {
            background-color: #1a365d;
            color: #fff;
            padding: 2rem 0 1rem;
            font-family: 'Montserrat', sans-serif;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .footer-bottom {
            border-top: 1px solid rgba(233, 185, 73, 0.3);
            padding-top: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 1rem;
        }

        .footer-bottom p {
            color: #e2e8f0;
            margin: 0;
            font-size: 0.9rem;
        }

                /* Password toggle styles */
        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #1a365d; /* same color as key icon */
            font-size: 1rem;
            z-index: 2;
        }

        .toggle-password:hover {
            color: #e9b949;
        }

        /* Adjust input padding to fit both icons */
        .password-container input {
            padding-left: 45px;   /* space for key icon */
            padding-right: 45px;  /* space for eye icon */
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
            vertical-align: middle;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .address-loading {
            color: #1a365d;
            font-size: 14px;
            margin-top: 5px;
        }


        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-links {
                display: none;
            }
            
            .register-container {
                padding: 1rem;
            }
            
            .register-box {
                max-width: 100%;
            }
            
            .form-row, .address-row {
                flex-direction: column;
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .form-navigation {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
      <!-- Top Navigation -->
      <?php include '../includes/admin_navbar.php'; ?>

        <div class="register-container">
            <button onclick="openRegisterModal()" class="nav-button">
                <i class="fas fa-user-plus"></i> Register Account
            </button>

            <div id="registerModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeRegisterModal()">&times;</span>
            <div class="register-box">
                <div class="register-header">
                    <i class="fas fa-user-plus"></i>
                    <h1>Create Account</h1>
                    <p>Join Star Roofing & Construction</p>
                    
                    <!-- SweetAlert for PHP session errors -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Registration Failed',
                                text: '<?php echo $_SESSION['error']; ?>',
                            });
                        </script>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- SweetAlert for PHP session success -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: '<?php echo $_SESSION['success']; ?>',
                            });
                        </script>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <div class="progress-bar" id="progressBar">
                        <div class="progress-step active" id="step1">
                            1
                            <span class="step1-label">Biodata</span>
                        </div>
                        <div class="progress-step inactive" id="step2">
                            2
                            <span class="step2-label">Account</span>
                        </div>
                    </div>
                </div>
                
                <div class="register-body">
                    <form id="registerForm" action="../process/submit_info.php" method="POST">
                        <!-- Biodata Section -->
                        <div class="form-section active" id="section1">
                            <h2 style="color: #1a365d; margin-bottom: 1.5rem;">Personal Information</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" id="firstName" name="firstName" placeholder="Enter your first name" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" id="lastName" name="lastName" placeholder="Enter your last name" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="middleName">Middle Initial</label>
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" id="middleName" name="middleName" placeholder="Enter your middle initial" maxlength="4">
                                </div>
                                
                                <div class="form-group">
                                    <label for="birthdate">Date of Birth</label>
                                    <i class="fas fa-calendar input-icon"></i>
                                    <input type="date" id="birthdate" name="birthdate" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contactNumber">Contact Number</label>
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel" id="contactNumber" name="contactNumber" placeholder="e.g. 09123456789" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <i class="fas fa-venus-mars input-icon"></i>
                                    <select id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                            </div>
                            
                            <h2 style="color: #1a365d; margin: 2rem 0 1.5rem;">Address Information</h2>
                            
                            <div class="address-row">
                                <div class="form-group">
                                    <label for="region">Region</label>
                                    <i class="fas fa-map input-icon"></i>
                                    <select id="region" name="region" required>
                                        <option value="">Select Region</option>
                                    </select>
                                    <input type="hidden" id="region_name" name="region_name">
                                </div>
                                
                                <div class="form-group">
                                    <label for="province">Province</label>
                                    <i class="fas fa-map input-icon"></i>
                                    <select id="province" name="province" required disabled>
                                        <option value="">Select Province</option>
                                    </select>
                                    <input type="hidden" id="province_name" name="province_name">
                                </div>
                            </div>
                            
                            <div class="address-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <i class="fas fa-city input-icon"></i>
                                    <select id="city" name="city" required disabled>
                                        <option value="">Select City</option>
                                    </select>
                                    <input type="hidden" id="city_name" name="city_name">
                                </div>
                                
                                <div class="form-group">
                                    <label for="barangay">Barangay</label>
                                    <i class="fas fa-map-marker input-icon"></i>
                                    <select id="barangay" name="barangay" required disabled>
                                        <option value="">Select Barangay</option>
                                    </select>
                                    <input type="hidden" id="barangay_name" name="barangay_name">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="street">Street Address</label>
                                <i class="fas fa-road input-icon"></i>
                                <textarea id="street" name="street" placeholder="House No., Street Name, Subdivision, etc." required></textarea>
                            </div>
                            
                            <div class="form-navigation">
                                <div></div>
                                <button type="button" class="nav-button next" id="nextToAccount">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Account Information Section -->
                        <div class="form-section" id="section2">
                            <h2 style="color: #1a365d; margin-bottom: 1.5rem;">Account Information</h2>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <i class="fas fa-envelope envelope-icon"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                            </div>
                            
                            <div class="form-group">
                                <div class="password-container">
                                    <label for="password">Password</label>
                                    <i class="fas fa-lock password-icon"></i>
                                    <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                                    <div class="password-strength">
                                        <div class="password-strength-bar"></div>
                                    </div>
                                    <div class="password-feedback" id="passwordFeedback"></div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="password-container">
                                    <label for="confirmPassword">Confirm Password</label>
                                    <i class="fas fa-lock password-icon"></i>
                                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                                    <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                                    <div id="confirmFeedback"></div>
                                </div>
                            </div>

                            <div class="terms">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                            </div>
                            
                            <div class="form-navigation">
                                <button type="button" class="nav-button prev" id="backToBiodata">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                                <button type="submit" class="nav-button" id="createAccountBtn">
                                    Create Account <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
  </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../javascript/ph-address-selector.js"></script>
    <script src="../javascript/register-form.js"></script>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const input = document.getElementById('confirmPassword');
            input.type = input.type === 'password' ? 'text' : 'password';
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Next to Account
        document.getElementById('nextToAccount').addEventListener('click', function() {
            const requiredFields = ['firstName','lastName','birthdate','contactNumber','gender','region','province','city','barangay','street'];
            let isValid = true;
            requiredFields.forEach(id => {
                if (!document.getElementById(id).value) isValid = false;
            });
            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Form',
                    text: 'Please fill out all required fields in the Biodata section.',
                });
                return;
            }
            document.getElementById('section1').classList.remove('active');
            document.getElementById('section2').classList.add('active');
            document.getElementById('step1').classList.replace('active','inactive');
            document.getElementById('step2').classList.replace('inactive','active');
            document.getElementById('progressBar').classList.add('step-2-active');
        });

        // Back to Biodata
        document.getElementById('backToBiodata').addEventListener('click', function() {
            document.getElementById('section2').classList.remove('active');
            document.getElementById('section1').classList.add('active');
            document.getElementById('step2').classList.replace('active','inactive');
            document.getElementById('step1').classList.replace('inactive','active');
            document.getElementById('progressBar').classList.remove('step-2-active');
        });

        // Confirm password validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const pass = document.getElementById('password').value;
            const confirm = this.value;
            const feedback = document.getElementById('confirmFeedback');
            if (confirm.length > 0) {
                feedback.innerHTML = pass !== confirm 
                    ? '<span style="color:#e53e3e;">Passwords do not match</span>'
                    : '<span style="color:#38a169;">Passwords match</span>';
            } else {
                feedback.textContent = '';
            }
        });

        // Email availability check (AJAX)
        $('#email').on('blur', function () {
            let email = $(this).val().trim();
            if (email.length > 0) {
                $.post('../process/check_email.php', { email: email }, function (response) {
                    if (response === "exists") {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Email Already Exists',
                            text: 'This email address is already registered. Please use a different one.',
                        });
                        $('#email').val('');
                        $('#createAccountBtn').prop('disabled', true);
                    } else {
                        $('#createAccountBtn').prop('disabled', false);
                    }
                });
            }
        });

        // Submit form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirmPassword').value;
            const terms = document.getElementById('terms').checked;

            if (pass !== confirm) {
                Swal.fire({ icon: 'error', title: 'Password Mismatch', text: 'Passwords do not match.' });
                return;
            }
            if (!terms) {
                Swal.fire({ icon: 'warning', title: 'Terms Required', text: 'You must agree to the Terms and Privacy Policy.' });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Processing...',
                text: 'Creating your account, please wait.',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            e.target.submit();
        });
    </script>
    <script>
function openRegisterModal() {
    document.getElementById("registerModal").style.display = "block";
}
function closeRegisterModal() {
    document.getElementById("registerModal").style.display = "none";
}
// Close when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById("registerModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>
</body>
</html>
