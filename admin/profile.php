<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        }
        
        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: #1a365d;
            color: white;
            padding: 1.5rem 0;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-logo {
            width: 150px;
            margin-bottom: 1rem;
        }
        
        .sidebar-title {
            font-size: 1.2rem;
            color: #e9b949;
            font-weight: 600;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 1.5rem 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: #e2e8f0;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(233, 185, 73, 0.1);
            color: #e9b949;
            border-left-color: #e9b949;
        }
        
        .sidebar-menu i {
            margin-right: 0.8rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Area */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Top Navigation */
        .top-navbar {
            background: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .breadcrumb a {
            color: #4299e1;
            text-decoration: none;
        }
        
        .breadcrumb span {
            color: #718096;
            margin: 0 0.5rem;
        }
        
        /* User Profile Dropdown */
        .user-profile {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e9b949;
        }
        
        .user-name {
            font-weight: 600;
            color: #1a365d;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            width: 200px;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
            z-index: 1000;
            display: none;
            margin-top: 0.5rem;
        }
        
        .user-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .dropdown-item:hover {
            background: #f5f7f9;
            color: #1a365d;
        }
        
        .dropdown-item i {
            margin-right: 0.8rem;
            color: #e9b949;
            width: 16px;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 0.3rem 0;
        }
        
        /* Profile Content */
        .profile-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: #1a365d;
            font-weight: 700;
        }
        
        .page-description {
            color: #718096;
            margin-top: 0.5rem;
        }
        
        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .profile-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .profile-card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a365d;
        }
        
        .profile-card-body {
            padding: 1.5rem;
        }
        
        /* Profile Form */
        .profile-form {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        .profile-picture-section {
            text-align: center;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e9b949;
            margin-bottom: 1rem;
        }
        
        .picture-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.6rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #1a365d;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2c5282;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #e2e8f0;
            color: #4a5568;
        }
        
        .btn-outline:hover {
            background: #f5f7f9;
        }
        
        .profile-details-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1a365d;
            font-size: 0.9rem;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
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
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .profile-form {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .profile-picture-section {
                order: -1;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }
        
        @media (max-width: 768px) {
            .profile-details-section {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .breadcrumb {
                display: none;
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
            
            <!-- Profile Content -->
            <div class="profile-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">My Profile</h1>
                        <p class="page-description">Manage your account settings and profile information</p>
                    </div>
                </div>
                
                <div class="profile-card">
                    <div class="profile-card-header">
                        <h2 class="profile-card-title">Personal Information</h2>
                    </div>
                    <div class="profile-card-body">
                        <form class="profile-form">
                            <div class="profile-picture-section">
                                <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Profile Picture" class="profile-picture" id="profilePicture">
                                <div class="picture-actions">
                                    <button type="button" class="btn btn-primary" id="changePictureBtn">
                                        <i class="fas fa-camera"></i> Change Picture
                                    </button>
                                    <button type="button" class="btn btn-outline">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                    <input type="file" id="pictureUpload" accept="image/*" style="display: none;">
                                </div>
                            </div>
                            
                            <div class="profile-details-section">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" value="Admin" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" value="User" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" value="admin@starroofing.com" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" value="+63 912 345 6789">
                                </div>
                                
                                <div class="form-group">
                                    <label for="position">Position</label>
                                    <input type="text" id="position" value="System Administrator">
                                </div>
                                
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <select id="department">
                                        <option value="management">Management</option>
                                        <option value="operations" selected>Operations</option>
                                        <option value="sales">Sales</option>
                                        <option value="support">Customer Support</option>
                                        <option value="finance">Finance</option>
                                    </select>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="button" class="btn btn-outline">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="profile-card">
                    <div class="profile-card-header">
                        <h2 class="profile-card-title">Security Settings</h2>
                    </div>
                    <div class="profile-card-body">
                        <form class="profile-form">
                            <div class="profile-details-section">
                                <div class="form-group">
                                    <label for="currentPassword">Current Password</label>
                                    <input type="password" id="currentPassword">
                                </div>
                                
                                <div class="form-group">
                                    <label for="newPassword">New Password</label>
                                    <input type="password" id="newPassword">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirmPassword">Confirm New Password</label>
                                    <input type="password" id="confirmPassword">
                                </div>
                                
                                <div class="form-group full-width">
                                    <div class="form-actions">
                                        <button type="button" class="btn btn-outline">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-lock"></i> Update Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Profile picture change functionality
        document.getElementById('changePictureBtn').addEventListener('click', function() {
            document.getElementById('pictureUpload').click();
        });
        
        document.getElementById('pictureUpload').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('profilePicture').src = e.target.result;
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        
        // Form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Profile updated successfully!');
            });
        });
    </script>
</body>
</html>