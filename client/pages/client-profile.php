<?php
// client/pages/client-profile.php
session_start();

// Manual auth check with correct redirect
if (!isset($_SESSION['account_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../../public/login.php");
    exit();
}

// Get user information from session
$userId = $_SESSION['account_id'];
$userEmail = $_SESSION['email'];
$firstName = $_SESSION['first_name'] ?? '';
$lastName = $_SESSION['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);

// Get initials for avatar
$initials = '';
if (!empty($firstName)) {
    $initials .= strtoupper(substr($firstName, 0, 1));
}
if (!empty($lastName)) {
    $initials .= strtoupper(substr($lastName, 0, 1));
}
if (empty($initials)) {
    $initials = 'U';
}

// Connect to database to get full profile
require_once '../../database/starroofing_db.php';

$sql = "SELECT up.*, a.email, a.account_status, a.created_at 
        FROM user_profiles up 
        JOIN accounts a ON up.account_id = a.id 
        WHERE up.account_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

// If no profile exists, use session data
if (!$profile) {
    $profile = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $userEmail,
        'contact_number' => '',
        'birthdate' => '',
        'gender' => '',
        'street' => '',
        'barangay_name' => '',
        'city_name' => '',
        'province_name' => '',
        'region_name' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #0a0a0a;
            color: #ffffff;
            line-height: 1.6;
            padding: 2rem;
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .profile-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f0f 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e9b949, #d4a847);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 800;
            color: #1a1a2e;
            flex-shrink: 0;
            position: relative;
        }

        .status-indicator {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 24px;
            height: 24px;
            background: #10b981;
            border: 4px solid #0a0a0a;
            border-radius: 50%;
        }

        .profile-info h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .profile-info p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 1rem;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        .btn-primary {
            background: #e9b949;
            color: #1a1a2e;
        }

        .btn-primary:hover {
            background: #d4a847;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 0.5rem;
        }

        .form-group input {
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(233, 185, 73, 0.5);
            box-shadow: 0 0 0 3px rgba(233, 185, 73, 0.1);
        }

        .form-group input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .hidden {
            display: none !important;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #e9b949;
            text-decoration: none;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            gap: 1rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
                padding: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <a href="../../index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Home</span>
        </a>

        <header class="profile-header">
            <div class="profile-avatar">
                <?php echo $initials; ?>
                <span class="status-indicator"></span>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($fullName ?: 'User Profile'); ?></h1>
                <p><?php echo htmlspecialchars($userEmail); ?></p>
            </div>
        </header>

        <article class="info-card">
            <div class="card-header">
                <h2 class="card-title">Personal Information</h2>
                <button class="btn btn-primary" id="editBtn" onclick="toggleEdit()">Edit Profile</button>
            </div>
            <form class="form-grid" id="profileForm" method="POST" action="update_profile.php">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="first_name" 
                           value="<?php echo htmlspecialchars($profile['first_name']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="last_name" 
                           value="<?php echo htmlspecialchars($profile['last_name']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($profile['email']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="phone">Contact Number</label>
                    <input type="tel" id="phone" name="contact_number" 
                           value="<?php echo htmlspecialchars($profile['contact_number']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate" 
                           value="<?php echo htmlspecialchars($profile['birthdate']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <input type="text" id="gender" name="gender" 
                           value="<?php echo htmlspecialchars($profile['gender']); ?>" disabled>
                </div>
            </form>
            <div id="editActions" class="hidden" style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                <button class="btn btn-primary" onclick="saveProfile()">Save Changes</button>
                <button class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
            </div>
        </article>

        <article class="info-card">
            <h2 class="card-title" style="margin-bottom: 1.5rem;">Address Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Street</label>
                    <input type="text" value="<?php echo htmlspecialchars($profile['street']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Barangay</label>
                    <input type="text" value="<?php echo htmlspecialchars($profile['barangay_name']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" value="<?php echo htmlspecialchars($profile['city_name']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Province</label>
                    <input type="text" value="<?php echo htmlspecialchars($profile['province_name']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Region</label>
                    <input type="text" value="<?php echo htmlspecialchars($profile['region_name']); ?>" disabled>
                </div>
            </div>
        </article>

        <button class="btn btn-secondary" style="width: 100%;" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let isEditing = false;
        
        function toggleEdit() {
            isEditing = true;
            const inputs = document.querySelectorAll('#profileForm input');
            inputs.forEach(input => {
                if (input.id !== 'email') {
                    input.disabled = false;
                }
            });
            
            document.getElementById('editBtn').style.display = 'none';
            document.getElementById('editActions').classList.remove('hidden');
        }

        function saveProfile() {
            const formData = new FormData(document.getElementById('profileForm'));
            
            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Updated!',
                        text: 'Your profile has been updated successfully.',
                        confirmButtonColor: '#e9b949'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: data.message || 'Failed to update profile.',
                        confirmButtonColor: '#e9b949'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating your profile.',
                    confirmButtonColor: '#e9b949'
                });
            });
        }

        function cancelEdit() {
            location.reload();
        }

        function logout() {
            Swal.fire({
                title: 'Logout',
                text: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e9b949',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../../public/logout.php';
                }
            });
        }
    </script>
</body>
</html>