// Form navigation
document.getElementById('nextToAccount').addEventListener('click', function() {
    // Validate biodata section first
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const birthdate = document.getElementById('birthdate').value;
    const contactNumber = document.getElementById('contactNumber').value;
    const gender = document.getElementById('gender').value;
    const region = document.getElementById('region').value;
    const province = document.getElementById('province').value;
    const city = document.getElementById('city').value;
    const barangay = document.getElementById('barangay').value;
    
    if (!firstName || !lastName || !birthdate || !contactNumber || !gender || 
        !region || !province || !city || !barangay) {
        alert('Please fill out all required fields in the Biodata section');
        return;
    }
    
    // Switch to account section
    document.getElementById('section1').classList.remove('active');
    document.getElementById('section2').classList.add('active');
    
    // Update progress bar
    document.getElementById('step1').classList.remove('active');
    document.getElementById('step1').classList.add('inactive');
    document.getElementById('step2').classList.remove('inactive');
    document.getElementById('step2').classList.add('active');
    document.getElementById('progressBar').classList.add('step-2-active');
});

document.getElementById('backToBiodata').addEventListener('click', function() {
    // Switch back to biodata section
    document.getElementById('section2').classList.remove('active');
    document.getElementById('section1').classList.add('active');
    
    // Update progress bar
    document.getElementById('step2').classList.remove('active');
    document.getElementById('step2').classList.add('inactive');
    document.getElementById('step1').classList.remove('inactive');
    document.getElementById('step1').classList.add('active');
    document.getElementById('progressBar').classList.remove('step-2-active');
});

// Email validation
document.getElementById('email').addEventListener('blur', function() {
    const email = this.value;
    const feedback = document.getElementById('emailFeedback');
    
    if (email.length > 0) {
        // Simple email validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            feedback.textContent = 'Please enter a valid email address';
        } else {
            // Check if email exists via AJAX
            feedback.innerHTML = '<span style="color: #38a169;">Checking availability...</span>';
            
            fetch('../ajax/check_email.php?email=' + encodeURIComponent(email))
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        feedback.textContent = 'This email is already registered';
                    } else {
                        feedback.innerHTML = '<span style="color: #38a169;">Email is available</span>';
                    }
                })
                .catch(error => {
                    feedback.textContent = 'Error checking email availability';
                });
        }
    } else {
        feedback.textContent = '';
    }
});

// Password strength meter
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.querySelector('.password-strength-bar');
    const feedback = document.getElementById('passwordFeedback');
    let strength = 0;
    let feedbackText = '';
    
    // Reset classes
    strengthBar.parentElement.className = 'password-strength';
    
    if (password.length > 0) {
        // Check password strength
        if (password.length < 6) {
            feedbackText = 'Password is too short';
        } else {
            strength += 1;
            
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Apply visual feedback
            if (strength < 2) {
                strengthBar.parentElement.classList.add('password-weak');
                feedbackText = 'Weak password';
            } else if (strength < 4) {
                strengthBar.parentElement.classList.add('password-medium');
                feedbackText = 'Medium strength password';
            } else {
                strengthBar.parentElement.classList.add('password-strong');
                feedbackText = 'Strong password';
            }
        }
    }
    
    feedback.textContent = feedbackText;
});

// Confirm password validation
document.getElementById('confirmPassword').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const feedback = document.getElementById('confirmFeedback');
    
    if (confirmPassword.length > 0) {
        if (password !== confirmPassword) {
            feedback.innerHTML = '<span style="color: #e53e3e;">Passwords do not match</span>';
        } else {
            feedback.innerHTML = '<span style="color: #38a169;">Passwords match</span>';
        }
    } else {
        feedback.textContent = '';
    }
});

// Form submission
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const terms = document.getElementById('terms').checked;
    
    // Validate email
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert('Please enter a valid email address');
        return;
    }
    
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters long');
        return;
    }
    
    if (!terms) {
        alert('You must agree to the Terms of Service and Privacy Policy');
        return;
    }
    this.submit();
});

document.getElementById('contactNumber').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, ''); // remove non-digits
});
