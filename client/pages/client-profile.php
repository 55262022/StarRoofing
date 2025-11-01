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
        'gender' => ''
    ];
}

// Get user addresses from new table
$addressSql = "SELECT * FROM user_addresses WHERE account_id = ? ORDER BY is_default DESC, created_at DESC";
$addressStmt = $conn->prepare($addressSql);
$addressStmt->bind_param("i", $userId);
$addressStmt->execute();
$addressResult = $addressStmt->get_result();
$addresses = $addressResult->fetch_all(MYSQLI_ASSOC);
$addressStmt->close();

// Auto-migrate old address if exists and no new addresses
if (empty($addresses) && $profile) {
    $hasOldAddress = !empty($profile['street']) || 
                     !empty($profile['barangay_name']) || 
                     !empty($profile['city_name']) || 
                     !empty($profile['province_name']);
    
    if ($hasOldAddress) {
        // Migrate old address to new table
        $migrateSql = "INSERT INTO user_addresses 
                      (account_id, address_label, street, barangay_code, barangay_name, 
                       city_code, city_name, province_code, province_name, region_code, 
                       region_name, is_default) 
                      VALUES (?, 'Home', ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $migrateStmt = $conn->prepare($migrateSql);
        $migrateStmt->bind_param("isssssssss", 
            $userId,
            $profile['street'],
            $profile['barangay_code'] ?? null,
            $profile['barangay_name'],
            $profile['city_code'] ?? null,
            $profile['city_name'],
            $profile['province_code'] ?? null,
            $profile['province_name'],
            $profile['region_code'] ?? null,
            $profile['region_name']
        );
        
        if ($migrateStmt->execute()) {
            // Reload addresses
            $addressStmt = $conn->prepare($addressSql);
            $addressStmt->bind_param("i", $userId);
            $addressStmt->execute();
            $addressResult = $addressStmt->get_result();
            $addresses = $addressResult->fetch_all(MYSQLI_ASSOC);
            $addressStmt->close();
        }
        $migrateStmt->close();
    }
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
            background-color: rgba(21, 21, 41, 0.95);
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.3);
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

        .form-group input, .form-group select {
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
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

        /* Address Card Styles */
        .address-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .address-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .address-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(233, 185, 73, 0.3);
        }

        .address-card.default {
            border-color: #e9b949;
            background: rgba(233, 185, 73, 0.1);
        }

        .address-label {
            font-size: 1.1rem;
            font-weight: 700;
            color: #e9b949;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .default-badge {
            background: #e9b949;
            color: #1a1a2e;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .address-details {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .address-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: rgba(255, 255, 255, 0.4);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        /* Custom SweetAlert Styling for Dark Theme */
        .swal2-popup {
            background: #1a1a2e !important;
            color: #ffffff !important;
        }

        .swal2-title {
            color: #e9b949 !important;
        }

        .swal2-html-container {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .swal2-input, .swal2-select, .swal2-textarea {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
        }

        /* Fix dropdown option colors */
        .swal2-select option {
            background: #1a1a2e !important;
            color: #ffffff !important;
        }

        .swal2-input:focus, .swal2-select:focus, .swal2-textarea:focus {
            border-color: rgba(233, 185, 73, 0.5) !important;
            box-shadow: 0 0 0 3px rgba(233, 185, 73, 0.1) !important;
        }

        .swal2-label {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        /* Additional styling for better readability */
        .swal2-select {
            padding: 0.75rem 1rem !important;
        }

        .swal2-textarea {
            padding: 0.75rem 1rem !important;
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

            .address-list {
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
            <div class="card-header">
                <h2 class="card-title">Saved Addresses</h2>
                <button class="btn btn-primary" onclick="openAddressModal()">
                    <i class="fas fa-plus"></i> Add Address
                </button>
            </div>
            
            <div class="address-list" id="addressList">
                <?php if (empty($addresses)): ?>
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>No addresses saved yet. Add your first address!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($addresses as $address): ?>
                        <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>" data-address-id="<?php echo $address['id']; ?>">
                            <div class="address-label">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($address['address_label']); ?>
                                <?php if ($address['is_default']): ?>
                                    <span class="default-badge">DEFAULT</span>
                                <?php endif; ?>
                            </div>
                            <div class="address-details">
                                <?php 
                                $parts = array_filter([
                                    $address['street'],
                                    $address['barangay_name'],
                                    $address['city_name'],
                                    $address['province_name'],
                                    $address['region_name']
                                ]);
                                echo htmlspecialchars(implode(', ', $parts));
                                ?>
                            </div>
                            <div class="address-actions">
                                <?php if (!$address['is_default']): ?>
                                    <button class="btn btn-secondary btn-sm" onclick="setDefaultAddress(<?php echo $address['id']; ?>)">
                                        <i class="fas fa-star"></i> Set Default
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-secondary btn-sm" onclick="editAddress(<?php echo $address['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteAddress(<?php echo $address['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <button class="btn btn-secondary" style="width: 100%;" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../javascript/inquiry-address-selector.js"></script>
    
    <script>
        let isEditing = false;
        let currentEditingAddressId = null;
        
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

        async function openAddressModal(addressId = null) {
            const isEdit = addressId !== null;
            currentEditingAddressId = addressId;
            let addressData = null;

            if (isEdit) {
                // Fetch address data for editing
                const response = await fetch(`get_address.php?id=${addressId}`);
                addressData = await response.json();
            }

            const { value: formValues } = await Swal.fire({
                title: isEdit ? 'Edit Address' : 'Add New Address',
                html: `
                    <style>
                        .address-form-container select {
                            background: rgba(255, 255, 255, 0.1) !important;
                            color: #ffffff !important;
                            border: 1px solid rgba(255, 255, 255, 0.2) !important;
                            padding: 0.75rem !important;
                            border-radius: 8px !important;
                        }
                        .address-form-container select option {
                            background: #2a2a3e !important;
                            color: #ffffff !important;
                            padding: 8px !important;
                        }
                        .address-form-container select option:hover {
                            background: #3a3a4e !important;
                        }
                        .address-form-container input,
                        .address-form-container textarea {
                            background: rgba(255, 255, 255, 0.1) !important;
                            color: #ffffff !important;
                            border: 1px solid rgba(255, 255, 255, 0.2) !important;
                            padding: 0.75rem !important;
                            border-radius: 8px !important;
                        }
                        .address-form-container label {
                            display: block;
                            margin-bottom: 0.5rem;
                            font-weight: 600;
                            color: rgba(255,255,255,0.8) !important;
                            text-align: left;
                        }
                    </style>
                    <div class="address-form-container" style="display: grid; gap: 1rem; text-align: left; max-height: 70vh; overflow-y: auto; padding-right: 10px;">
                        <div>
                            <label>Address Label *</label>
                            <input id="swal-label" class="swal2-input" placeholder="e.g., Home, Work, Office" value="${isEdit && addressData ? addressData.address_label : ''}" style="width: 100%; margin: 0;" required>
                        </div>
                        
                        <div>
                            <label>Region *</label>
                            <select id="swal-region" class="swal2-select" style="width: 100%; margin: 0;" required>
                                <option value="">Select Region</option>
                            </select>
                        </div>
                        
                        <div>
                            <label>Province *</label>
                            <select id="swal-province" class="swal2-select" style="width: 100%; margin: 0;" disabled required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        
                        <div>
                            <label>City *</label>
                            <select id="swal-city" class="swal2-select" style="width: 100%; margin: 0;" disabled required>
                                <option value="">Select City</option>
                            </select>
                        </div>
                        
                        <div>
                            <label>Barangay *</label>
                            <select id="swal-barangay" class="swal2-select" style="width: 100%; margin: 0;" disabled required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        
                        <div>
                            <label>Street</label>
                            <textarea id="swal-street" class="swal2-textarea" placeholder="House No., Street Name, Subdivision, etc." style="width: 100%; margin: 0; min-height: 80px;">${isEdit && addressData ? (addressData.street || '') : ''}</textarea>
                        </div>
                    </div>
                `,
                width: '600px',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonColor: '#e9b949',
                cancelButtonColor: '#6c757d',
                confirmButtonText: isEdit ? 'Update Address' : 'Add Address',
                didOpen: () => {
                    // Initialize cascading selectors after modal opens
                    initializeAddressSelectors(isEdit ? addressData : null);
                },
                preConfirm: () => {
                    const label = document.getElementById('swal-label').value;
                    const regionSelect = document.getElementById('swal-region');
                    const provinceSelect = document.getElementById('swal-province');
                    const citySelect = document.getElementById('swal-city');
                    const barangaySelect = document.getElementById('swal-barangay');
                    const street = document.getElementById('swal-street').value;

                    if (!label || !regionSelect.value || !provinceSelect.value || !citySelect.value || !barangaySelect.value) {
                        Swal.showValidationMessage('Please fill all required fields');
                        return false;
                    }

                    return {
                        label: label,
                        region_code: regionSelect.value,
                        region_name: regionSelect.options[regionSelect.selectedIndex].text,
                        province_code: provinceSelect.value,
                        province_name: provinceSelect.options[provinceSelect.selectedIndex].text,
                        city_code: citySelect.value,
                        city_name: citySelect.options[citySelect.selectedIndex].text,
                        barangay_code: barangaySelect.value,
                        barangay_name: barangaySelect.options[barangaySelect.selectedIndex].text,
                        street: street
                    }
                }
            });

            if (formValues) {
                saveAddress(formValues, addressId);
            }
        }

        function initializeAddressSelectors(addressData = null) {
            const regionSelect = $('#swal-region');
            const provinceSelect = $('#swal-province');
            const citySelect = $('#swal-city');
            const barangaySelect = $('#swal-barangay');

            // Setup cascading dropdowns (reuse your inquiry-address-selector.js logic)
            regionSelect.on('change', function() {
                const regionCode = $(this).val();
                provinceSelect.prop('disabled', true).html('<option value="">Loading...</option>');
                citySelect.prop('disabled', true).html('<option value="">Select City</option>');
                barangaySelect.prop('disabled', true).html('<option value="">Select Barangay</option>');

                if (regionCode) {
                    fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`)
                        .then(res => res.json())
                        .then(data => {
                            provinceSelect.html('<option value="">Select Province</option>');
                            data.forEach(item => {
                                provinceSelect.append(`<option value="${item.code}">${item.name}</option>`);
                            });
                            provinceSelect.prop('disabled', false);

                            if (addressData && addressData.province_code) {
                                provinceSelect.val(addressData.province_code).trigger('change');
                            }
                        });
                }
            });

            provinceSelect.on('change', function() {
                const provinceCode = $(this).val();
                citySelect.prop('disabled', true).html('<option value="">Loading...</option>');
                barangaySelect.prop('disabled', true).html('<option value="">Select Barangay</option>');

                if (provinceCode) {
                    fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`)
                        .then(res => res.json())
                        .then(data => {
                            citySelect.html('<option value="">Select City</option>');
                            data.forEach(item => {
                                citySelect.append(`<option value="${item.code}">${item.name}</option>`);
                            });
                            citySelect.prop('disabled', false);

                            if (addressData && addressData.city_code) {
                                citySelect.val(addressData.city_code).trigger('change');
                            }
                        });
                }
            });

            citySelect.on('change', function() {
                const cityCode = $(this).val();
                barangaySelect.prop('disabled', true).html('<option value="">Loading...</option>');

                if (cityCode) {
                    fetch(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`)
                        .then(res => res.json())
                        .then(data => {
                            barangaySelect.html('<option value="">Select Barangay</option>');
                            data.forEach(item => {
                                barangaySelect.append(`<option value="${item.code}">${item.name}</option>`);
                            });
                            barangaySelect.prop('disabled', false);

                            if (addressData && addressData.barangay_code) {
                                barangaySelect.val(addressData.barangay_code);
                            }
                        });
                }
            });

            // Load regions initially
            fetch('https://psgc.gitlab.io/api/regions/')
                .then(res => res.json())
                .then(data => {
                    regionSelect.html('<option value="">Select Region</option>');
                    data.forEach(item => {
                        regionSelect.append(`<option value="${item.code}">${item.name}</option>`);
                    });

                    // Pre-populate if editing
                    if (addressData && addressData.region_code) {
                        setTimeout(() => {
                            regionSelect.val(addressData.region_code).trigger('change');
                        }, 100);
                    }
                });
        }

        function saveAddress(data, addressId = null) {
            const formData = new FormData();
            formData.append('address_label', data.label);
            formData.append('street', data.street);
            formData.append('barangay_code', data.barangay_code);
            formData.append('barangay_name', data.barangay_name);
            formData.append('city_code', data.city_code);
            formData.append('city_name', data.city_name);
            formData.append('province_code', data.province_code);
            formData.append('province_name', data.province_name);
            formData.append('region_code', data.region_code);
            formData.append('region_name', data.region_name);
            
            if (addressId) {
                formData.append('address_id', addressId);
            }

            fetch('manage_address.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: addressId ? 'Address updated successfully!' : 'Address added successfully!',
                        confirmButtonColor: '#e9b949'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to save address.',
                        confirmButtonColor: '#e9b949'
                    });
                }
            });
        }

        function editAddress(addressId) {
            openAddressModal(addressId);
        }

        function deleteAddress(addressId) {
            Swal.fire({
                title: 'Delete Address?',
                text: 'Are you sure you want to delete this address?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('delete_address', addressId);

                    fetch('manage_address.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Address has been deleted.',
                                confirmButtonColor: '#e9b949'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to delete address.',
                                confirmButtonColor: '#e9b949'
                            });
                        }
                    });
                }
            });
        }

        function setDefaultAddress(addressId) {
            const formData = new FormData();
            formData.append('set_default', addressId);

            fetch('manage_address.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Default address updated!',
                        confirmButtonColor: '#e9b949',
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to set default address.',
                        confirmButtonColor: '#e9b949'
                    });
                }
            });
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