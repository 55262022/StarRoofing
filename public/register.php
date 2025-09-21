<?php
session_start();
include '../database/starroofing_db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/register.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include '../includes/navbar.php'?>

    <div class="register-container">
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
</body>
</html>
