<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Email Verification - Star Roofing & Construction</title>
  
  <!-- Styles -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Montserrat', sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
      color: #2c3e50;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .email-container {
      background-color: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 500px;
      text-align: center;
    }
    
    .logo {
      margin-bottom: 20px;
    }
    
    .logo i {
      font-size: 48px;
      color: #3498db;
    }
    
    .title {
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }
    
    .title h2 {
      font-size: 24px;
      font-weight: 600;
      color: #2c3e50;
    }
    
    .title p {
      color: #7f8c8d;
      margin-top: 10px;
      font-size: 14px;
    }
    
    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #34495e;
    }
    
    .email-input-container {
      position: relative;
      display: flex;
      align-items: center;
    }
    
    .email-input-container i {
      position: absolute;
      left: 15px;
      color: #7f8c8d;
    }
    
    .email-input {
      width: 100%;
      padding: 12px 15px 12px 45px;
      border: 2px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s;
    }
    
    .email-input:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }
    
    .validation-status {
      margin-top: 8px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 5px;
      min-height: 20px;
    }
    
    .valid {
      color: #27ae60;
    }
    
    .invalid {
      color: #e74c3c;
    }
    
    .btn-primary {
      background-color: #3498db;
      color: white;
      border: none;
      padding: 14px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      font-size: 16px;
      transition: background-color 0.3s;
      width: 100%;
      margin-top: 10px;
    }
    
    .btn-primary:hover {
      background-color: #2980b9;
    }
    
    .btn-primary:disabled {
      background-color: #bdc3c7;
      cursor: not-allowed;
    }
    
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(52, 152, 219, 0.3);
      border-radius: 50%;
      border-top-color: #3498db;
      animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    .success-checkmark {
      color: #27ae60;
      font-size: 18px;
    }
    
    .footer {
      margin-top: 20px;
      font-size: 12px;
      color: #7f8c8d;
    }
    
    .debug-info {
      margin-top: 15px;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 5px;
      font-size: 12px;
      text-align: left;
      display: none;
    }
  </style>
</head>

<body>
  <div class="email-container">
    <div class="logo">
      <i class="fas fa-home"></i>
    </div>
    
    <div class="title">
      <h2>Admin Account Verification</h2>
      <p>Enter a Google email to create a new admin account</p>
    </div>

    <form id="emailForm">
      <div class="form-group">
        <label for="adminEmail">Email Address</label>
        <div class="email-input-container">
          <i class="fas fa-envelope"></i>
          <input type="email" id="adminEmail" class="email-input" placeholder="Enter Google email address" required autocomplete="off">
        </div>
        <div id="validationStatus" class="validation-status"></div>
      </div>
      
      <button type="submit" class="btn-primary" id="verifyBtn" disabled>Verify & Create Account</button>
    </form>
    
    <div class="footer">
      <p>© 2025 Star Roofing & Construction. All rights reserved.</p>
    </div>
    
    <div class="debug-info" id="debugInfo">
      <p><strong>Debug Information:</strong></p>
      <p id="debugStatus">No errors detected</p>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      const emailInput = $('#adminEmail');
      const validationStatus = $('#validationStatus');
      const verifyBtn = $('#verifyBtn');
      const debugInfo = $('#debugInfo');
      const debugStatus = $('#debugStatus');
      let isValidFormat = false;
      let isGoogleAccount = false;
      let existsInDB = false;

      // Validate email on input
      emailInput.on('input', function() {
        const email = $(this).val().trim();
        
        // Reset states
        isValidFormat = false;
        isGoogleAccount = false;
        existsInDB = false;
        verifyBtn.prop('disabled', true);
        validationStatus.removeClass('valid invalid').html('');
        debugInfo.hide();
        
        if (email === '') {
          return;
        }
        
        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        isValidFormat = emailRegex.test(email);
        
        if (!isValidFormat) {
          validationStatus.addClass('invalid').html('<i class="fas fa-times-circle"></i> Invalid email format');
          return;
        }
        
        // Check if it's a Google account
        const domain = email.split('@')[1];
        isGoogleAccount = domain === 'gmail.com' || domain === 'googlemail.com' || 
                          domain.endsWith('.gmail.com') || domain === 'google.com';
        
        if (!isGoogleAccount) {
          validationStatus.addClass('invalid').html('<i class="fas fa-times-circle"></i> Please use a Google account (Gmail)');
          return;
        }
        
        // Show loading state while checking database
        validationStatus.addClass('valid').html('<div class="loading"></div> Checking availability...');
        
        // Check if email exists in database via AJAX
        $.ajax({
          url: 'check_email.php',
          method: 'POST',
          data: { email: email },
          success: function(response) {
            if (response.success) {
              existsInDB = response.exists;
              
              if (existsInDB) {
                validationStatus.addClass('invalid').html('<i class="fas fa-times-circle"></i> This email is already registered in our system');
              } else {
                validationStatus.addClass('valid').html('<i class="fas fa-check-circle success-checkmark"></i> Valid Google account and available');
                verifyBtn.prop('disabled', false);
              }
            } else {
              validationStatus.addClass('invalid').html('<i class="fas fa-exclamation-triangle"></i> ' + response.error);
              debugInfo.show();
              debugStatus.text('Server Error: ' + response.error);
            }
          },
          error: function(xhr, status, error) {
            validationStatus.addClass('invalid').html('<i class="fas fa-exclamation-triangle"></i> Error checking database');
            debugInfo.show();
            debugStatus.text('AJAX Error: ' + error + ' | Status: ' + status);
            console.error("AJAX error:", status, error);
          }
        });
      });
      
      // Form submission
      $('#emailForm').on('submit', function(e) {
        e.preventDefault();
        
        const email = emailInput.val().trim();
        
        if (!isValidFormat || !isGoogleAccount || existsInDB) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Email',
            text: 'Please enter a valid Google email that is not already in our system.'
          });
          return;
        }
        
        // Show confirmation
        Swal.fire({
          title: 'Create Admin Account',
          html: `You are about to create an admin account for:<br><strong>${email}</strong>`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3498db',
          cancelButtonColor: '#7f8c8d',
          confirmButtonText: 'Yes, create account',
          cancelButtonText: 'Cancel',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
              title: 'Creating Account',
              text: 'Please wait while we create the account...',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
            
            // Submit the form via AJAX to create_account.php
            $.ajax({
              url: 'create_account.php',
              method: 'POST',
              data: { email: email },
              success: function(response) {
                if (response.success) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Account Created!',
                    html: `Admin account for <strong>${email}</strong> has been created successfully.<br><br>
                          ${response.message}`,
                    confirmButtonColor: '#3498db'
                  }).then(() => {
                    // Reset form
                    emailInput.val('');
                    validationStatus.html('');
                    verifyBtn.prop('disabled', true);
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Creation Failed',
                    text: response.error || 'Failed to create account. Please try again.',
                    confirmButtonColor: '#3498db'
                  });
                }
              },
              error: function(xhr, status, error) {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'An error occurred while creating the account. Please try again.',
                  confirmButtonColor: '#3498db'
                });
                console.error("AJAX error:", status, error);
              }
            });
          }
        });
      });
    });
  </script>
</body>
</html>