<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Initialize variables
$status_filter = $_GET['status'] ?? 'all';
$search_term = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query for clients - FIXED to join with user_addresses
$query = "SELECT 
            up.id,
            up.first_name,
            up.last_name,
            up.middle_name,
            up.birthdate,
            up.contact_number,
            up.gender,
            ua.region_name,
            ua.province_name,
            ua.city_name,
            ua.barangay_name,
            ua.street,
            up.created_at,
            a.email,
            a.account_status,
            a.last_login
          FROM user_profiles up
          INNER JOIN accounts a ON up.account_id = a.id
          LEFT JOIN user_addresses ua ON a.id = ua.account_id AND ua.is_default = 1
          WHERE a.role_id = 2";

$count_query = "SELECT COUNT(*) as total 
                FROM user_profiles up
                INNER JOIN accounts a ON up.account_id = a.id
                WHERE a.role_id = 2";

if ($status_filter !== 'all') {
    $query .= " AND a.account_status = ?";
    $count_query .= " AND a.account_status = ?";
}

if (!empty($search_term)) {
    $search_like = "%$search_term%";
    $query .= " AND (up.first_name LIKE ? OR up.last_name LIKE ? OR a.email LIKE ? OR up.contact_number LIKE ?)";
    $count_query .= " AND (up.first_name LIKE ? OR up.last_name LIKE ? OR a.email LIKE ? OR up.contact_number LIKE ?)";
}

$query .= " ORDER BY up.created_at DESC LIMIT ? OFFSET ?";

// Get total count for pagination
$stmt_count = $conn->prepare($count_query);
if ($status_filter !== 'all' && !empty($search_term)) {
    $stmt_count->bind_param("sssss", $status_filter, $search_like, $search_like, $search_like, $search_like);
} elseif ($status_filter !== 'all') {
    $stmt_count->bind_param("s", $status_filter);
} elseif (!empty($search_term)) {
    $stmt_count->bind_param("ssss", $search_like, $search_like, $search_like, $search_like);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_clients = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_clients / $limit);

// Get clients
$stmt = $conn->prepare($query);
if ($status_filter !== 'all' && !empty($search_term)) {
    $stmt->bind_param("sssssii", $status_filter, $search_like, $search_like, $search_like, $search_like, $limit, $offset);
} elseif ($status_filter !== 'all') {
    $stmt->bind_param("sii", $status_filter, $limit, $offset);
} elseif (!empty($search_term)) {
    $stmt->bind_param("ssssii", $search_like, $search_like, $search_like, $search_like, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$clients = $result->fetch_all(MYSQLI_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_client') {
        // Add new client
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $middle_name = $_POST['middle_name'] ?? null;
        $birthdate = $_POST['birthdate'];
        $contact_number = $_POST['contact_number'] ?? null;
        $gender = $_POST['gender'];
        $region_code = $_POST['region_code'];
        $region_name = $_POST['region_name'];
        $province_code = $_POST['province_code'];
        $province_name = $_POST['province_name'];
        $city_code = $_POST['city_code'];
        $city_name = $_POST['city_name'];
        $barangay_code = $_POST['barangay_code'];
        $barangay_name = $_POST['barangay_name'];
        $street = $_POST['street'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $account_status = $_POST['account_status'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert into accounts table - FIXED role_id to 2 for clients
            $account_query = "INSERT INTO accounts (email, password, role_id, account_status) VALUES (?, ?, 2, ?)";
            $stmt_account = $conn->prepare($account_query);
            $stmt_account->bind_param("sss", $email, $password, $account_status);
            $stmt_account->execute();
            $account_id = $conn->insert_id;
            
            // Insert into user_profiles table - REMOVED address fields
            $profile_query = "INSERT INTO user_profiles (account_id, first_name, last_name, middle_name, birthdate, contact_number, gender) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_profile = $conn->prepare($profile_query);
            $stmt_profile->bind_param("issssss", $account_id, $first_name, $last_name, $middle_name, $birthdate, $contact_number, $gender);
            $stmt_profile->execute();
            
            // Insert into user_addresses table - NEW
            $address_query = "INSERT INTO user_addresses (account_id, address_label, street, barangay_code, barangay_name, city_code, city_name, province_code, province_name, region_code, region_name, is_default) VALUES (?, 'Home', ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt_address = $conn->prepare($address_query);
            $stmt_address->bind_param("isssssssss", $account_id, $street, $barangay_code, $barangay_name, $city_code, $city_name, $province_code, $province_name, $region_code, $region_name);
            $stmt_address->execute();
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['message'] = "Client added successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['message'] = "Error adding client: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
        
        header("Location: clients.php");
        exit();
    }
    
    if ($action === 'edit_client') {
        $client_id = $_POST['client_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $middle_name = $_POST['middle_name'];
        $birthdate = $_POST['birthdate'];
        $contact_number = $_POST['contact_number'];
        $gender = $_POST['gender'];
        $email = $_POST['email'];
        $account_status = $_POST['account_status'];
        
        // Address fields
        $street = $_POST['street'];
        $barangay_code = $_POST['barangay_code'];
        $barangay_name = $_POST['barangay_name'];
        $city_code = $_POST['city_code'];
        $city_name = $_POST['city_name'];
        $province_code = $_POST['province_code'];
        $province_name = $_POST['province_name'];
        $region_code = $_POST['region_code'];
        $region_name = $_POST['region_name'];

        $conn->begin_transaction();
        
        try {
            // Update accounts and user_profiles - REMOVED address fields from user_profiles
            $update_query = "UPDATE accounts a 
                            INNER JOIN user_profiles up ON a.id = up.account_id 
                            SET a.email=?, a.account_status=?, 
                                up.first_name=?, up.last_name=?, up.middle_name=?, 
                                up.birthdate=?, up.contact_number=?, up.gender=?
                            WHERE up.id=?";
            
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssssssi", 
                $email, $account_status, 
                $first_name, $last_name, $middle_name, 
                $birthdate, $contact_number, $gender,
                $client_id
            );
            $stmt->execute();
            
            // Get account_id from user_profiles
            $get_account_query = "SELECT account_id FROM user_profiles WHERE id = ?";
            $stmt_get = $conn->prepare($get_account_query);
            $stmt_get->bind_param("i", $client_id);
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            $account_data = $result_get->fetch_assoc();
            $account_id = $account_data['account_id'];
            
            // Update or Insert address in user_addresses
            $check_address = "SELECT id FROM user_addresses WHERE account_id = ? AND is_default = 1";
            $stmt_check = $conn->prepare($check_address);
            $stmt_check->bind_param("i", $account_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                // Update existing address
                $update_address = "UPDATE user_addresses 
                                  SET street=?, barangay_code=?, barangay_name=?, 
                                      city_code=?, city_name=?, province_code=?, 
                                      province_name=?, region_code=?, region_name=?
                                  WHERE account_id=? AND is_default=1";
                $stmt_address = $conn->prepare($update_address);
                $stmt_address->bind_param("sssssssssi", 
                    $street, $barangay_code, $barangay_name,
                    $city_code, $city_name, $province_code,
                    $province_name, $region_code, $region_name,
                    $account_id
                );
            } else {
                // Insert new address
                $insert_address = "INSERT INTO user_addresses 
                                  (account_id, address_label, street, barangay_code, barangay_name, 
                                   city_code, city_name, province_code, province_name, 
                                   region_code, region_name, is_default) 
                                  VALUES (?, 'Home', ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt_address = $conn->prepare($insert_address);
                $stmt_address->bind_param("isssssssss", 
                    $account_id, $street, $barangay_code, $barangay_name,
                    $city_code, $city_name, $province_code,
                    $province_name, $region_code, $region_name
                );
            }
            $stmt_address->execute();
            
            $conn->commit();
            
            $_SESSION['message'] = "Client updated successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error updating client: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }

        header("Location: clients.php");
        exit();
    }
    
    if ($action === 'update_status') {
        $client_id = $_POST['client_id'];
        $new_status = $_POST['account_status'];
        
        // Get account_id from user_profiles
        $get_account_query = "SELECT account_id FROM user_profiles WHERE id = ?";
        $stmt_get = $conn->prepare($get_account_query);
        $stmt_get->bind_param("i", $client_id);
        $stmt_get->execute();
        $result_get = $stmt_get->get_result();
        $account_data = $result_get->fetch_assoc();
        $account_id = $account_data['account_id'];
        
        // Update account status
        $update_query = "UPDATE accounts SET account_status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_status, $account_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Client status updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating status: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        
        header("Location: clients.php");
        exit();
    }
}

// Email check for AJAX
if (isset($_POST['check_email'])) {
    $email = $_POST['email'] ?? '';
    
    if (!empty($email)) {
        $query = "SELECT id FROM accounts WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "exists";
        } else {
            echo "available";
        }
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Management - Star Roofing & Construction</title>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS style -->
    <link rel="stylesheet" href="../css/admin_main.css">
    <style>
        .client-content {
            flex: 1;
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 5px 0;
        }
        
        .page-description {
            color: #7f8c8d;
            margin: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-warning {
            background-color: #dce73cff;
            color: black;
        }
        
        .btn-warning:hover {
            background-color: #b9c331ff;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #bdc3c7;
            color: #7f8c8d;
        }
        
        .btn-outline:hover {
            background-color: #f8f9fa;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* Search Form */
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            border-color: #007bff;
        }

        .search-btn {
            padding: 10px 18px;
            background-color: #3498db;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.2s ease;
        }

        .search-btn:hover {
            background-color: #2980b9;
        }
        
        .status-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .status-btn {
            padding: 8px 16px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .status-btn.active,
        .status-btn:hover {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        /* Table styles */
        .client-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .client-table th, .client-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .client-table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
        }

        .client-table tr:hover {
            background-color: #f9f9f9;
        }

        /* Pagination */
        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .page-btn {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid #3498db;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .page-btn:hover {
            background-color: #3498db;
            color: white;
        }

        .page-btn.active {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        .client-name {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 5px 0;
        }
        
        .client-email {
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.5;
            margin: 0 0 5px 0;
        }

        .detail-label {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status.active {
            background-color: #e8f6f3;
            color: #1abc9c;
        }
        
        .status.inactive {
            background-color: #fdedec;
            color: #e74c3c;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.3s;
        }
        
        .modal-close:hover {
            color: #34495e;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
            font-family: 'Montserrat', sans-serif;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #e8f6f3;
            color: #1abc9c;
            border: 1px solid #1abc9c;
        }
        
        .alert-error {
            background-color: #fdedec;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        
        .no-clients {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            background: white;
            border-radius: 10px;
        }
        
        .section-title {
            color: #2c3e50;
            margin: 30px 0 20px 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .address-group {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="main-content">
            <div class="client-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Clients Information</h1>
                        <p class="page-description">Manage your clients and their information</p>
                    </div>
                    <button class="btn btn-primary" id="addClientBtn">
                        <i class="fas fa-plus"></i> Add New Client
                    </button>
                </div>

                <!-- Display Messages -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                        <?= $_SESSION['message'] ?>
                    </div>
                    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                <?php endif; ?>

                <!-- Search Bar -->
                <form method="GET" action="" class="search-form">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                    <input type="text" name="search" placeholder="Search clients by name, email, or contact..." 
                        value="<?= htmlspecialchars($search_term) ?>" class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="search-btn" onclick="window.location='clients.php?status=<?= htmlspecialchars($status_filter) ?>'">
                        <i class="fas fa-times"></i> Reset
                    </button>
                </form>

                <!-- Status Filter -->
                <div class="status-filter">
                    <button class="status-btn <?= $status_filter === 'all' ? 'active' : '' ?>" data-status="all">All Clients</button>
                    <button class="status-btn <?= $status_filter === 'active' ? 'active' : '' ?>" data-status="active">Active</button>
                    <button class="status-btn <?= $status_filter === 'inactive' ? 'active' : '' ?>" data-status="inactive">Inactive</button>
                </div>
                
                <!-- Client Table -->
                <div class="client-container">
                    <div class="client-table">
                        <?php if (count($clients) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Registration Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client): ?>
                                        <tr>
                                            <td>
                                                <div class="client-name">
                                                    <?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>
                                                </div>
                                                <div class="detail-label">
                                                    <?= htmlspecialchars(ucfirst($client['gender'] ?? 'N/A')) ?> • 
                                                    <?= $client['birthdate'] ? date('M d, Y', strtotime($client['birthdate'])) : 'N/A' ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="client-email"><?= htmlspecialchars($client['email']) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($client['contact_number'] ?? 'N/A') ?></td>
                                            <td>
                                                <div class="detail-label">
                                                    <?= htmlspecialchars($client['city_name'] ?? 'N/A') ?>, 
                                                    <?= htmlspecialchars($client['province_name'] ?? 'N/A') ?>
                                                </div>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($client['created_at'])) ?></td>
                                            <td>
                                                <span class="status <?= $client['account_status'] ?>">
                                                    <?= ucfirst($client['account_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display:flex; gap:5px; flex-wrap: wrap;">
                                                    <button class="btn btn-warning edit-btn"
                                                        data-id="<?= $client['id'] ?>"
                                                        data-firstname="<?= htmlspecialchars($client['first_name']) ?>"
                                                        data-lastname="<?= htmlspecialchars($client['last_name']) ?>"
                                                        data-middlename="<?= htmlspecialchars($client['middle_name'] ?? '') ?>"
                                                        data-birthdate="<?= htmlspecialchars($client['birthdate'] ?? '') ?>"
                                                        data-contact="<?= htmlspecialchars($client['contact_number'] ?? '') ?>"
                                                        data-gender="<?= htmlspecialchars($client['gender'] ?? '') ?>"
                                                        data-email="<?= htmlspecialchars($client['email']) ?>"
                                                        data-status="<?= htmlspecialchars($client['account_status']) ?>"
                                                        data-street="<?= htmlspecialchars($client['street'] ?? '') ?>"
                                                        data-barangay="<?= htmlspecialchars($client['barangay_name'] ?? '') ?>"
                                                        data-city="<?= htmlspecialchars($client['city_name'] ?? '') ?>"
                                                        data-province="<?= htmlspecialchars($client['province_name'] ?? '') ?>"
                                                        data-region="<?= htmlspecialchars($client['region_name'] ?? '') ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-danger status-toggle-btn" 
                                                            data-id="<?= $client['id'] ?>" 
                                                            data-name="<?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>"
                                                            data-status="<?= $client['account_status'] ?>">
                                                        <?php if ($client['account_status'] === 'active'): ?>
                                                            <i class="fas fa-user-times"></i> Deactivate
                                                        <?php else: ?>
                                                            <i class="fas fa-user-check"></i> Activate
                                                        <?php endif; ?>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page-1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_term) ?>" class="page-btn">Prev</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_term) ?>" 
                                    class="page-btn <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?= $page+1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_term) ?>" class="page-btn">Next</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-clients">
                                <p>No clients found. Add your first client to get started.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Client Modal -->
    <div class="modal" id="addClientModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Client</h2>
                <button class="modal-close" id="closeAddModal">&times;</button>
            </div>
            <form id="addClientForm" action="clients.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_client">
                    
                    <div class="section-title">Personal Information</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="middleName">Middle Initial</label>
                            <input type="text" id="middleName" name="middle_name" maxlength="4" placeholder="e.g., A.">
                        </div>
                        <div class="form-group">
                            <label for="birthdate">Date of Birth *</label>
                            <input type="date" id="birthdate" name="birthdate" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="" disabled selected>Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="contactNumber">Contact Number</label>
                            <input type="tel" id="contactNumber" name="contact_number" maxlength="11" placeholder="09XXXXXXXXX" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>
                    
                    <div class="section-title">Address Information</div>
                    
                    <div class="address-group">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="region">Region *</label>
                                <select id="region" name="region_code" required>
                                    <option value="">Select Region</option>
                                </select>
                                <input type="hidden" id="region_name" name="region_name">
                            </div>
                            <div class="form-group">
                                <label for="province">Province *</label>
                                <select id="province" name="province_code" required disabled>
                                    <option value="">Select Province</option>
                                </select>
                                <input type="hidden" id="province_name" name="province_name">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <select id="city" name="city_code" required disabled>
                                    <option value="">Select City</option>
                                </select>
                                <input type="hidden" id="city_name" name="city_name">
                            </div>
                            <div class="form-group">
                                <label for="barangay">Barangay *</label>
                                <select id="barangay" name="barangay_code" required disabled>
                                    <option value="">Select Barangay</option>
                                </select>
                                <input type="hidden" id="barangay_name" name="barangay_name">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="street">Street Address *</label>
                            <textarea id="street" name="street" placeholder="House No., Street Name, Subdivision, etc." required></textarea>
                        </div>
                    </div>
                    
                    <div class="section-title">Account Information</div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                        <div id="emailFeedback" style="font-size: 12px; margin-top: 5px;"></div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password *</label>
                            <input type="password" id="confirmPassword" name="confirm_password" required>
                            <div id="passwordFeedback" style="font-size: 12px; margin-top: 5px;"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="account_status">Account Status *</label>
                        <select id="account_status" name="account_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" id="cancelAddBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveClientBtn">
                        Save Client
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Client Modal -->
    <div class="modal" id="editClientModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Client</h2>
                <button class="modal-close" id="closeEditModal">&times;</button>
            </div>
            <form id="editClientForm" action="clients.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_client">
                    <input type="hidden" name="client_id" id="editClientId">

                    <div class="section-title">Personal Information</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editFirstName">First Name *</label>
                            <input type="text" id="editFirstName" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="editLastName">Last Name *</label>
                            <input type="text" id="editLastName" name="last_name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editMiddleName">Middle Name</label>
                            <input type="text" id="editMiddleName" name="middle_name">
                        </div>
                        <div class="form-group">
                            <label for="editBirthdate">Birthdate *</label>
                            <input type="date" id="editBirthdate" name="birthdate" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editContact">Contact Number</label>
                            <input type="text" id="editContact" name="contact_number">
                        </div>
                        <div class="form-group">
                            <label for="editGender">Gender *</label>
                            <select id="editGender" name="gender" required>
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">Address Information</div>
                    
                    <div class="address-group">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="editRegion">Region</label>
                                <select id="editRegion" name="region_code" required>
                                    <option value="">Select Region</option>
                                </select>
                                <input type="hidden" id="editRegionName" name="region_name">
                            </div>
                            <div class="form-group">
                                <label for="editProvince">Province</label>
                                <select id="editProvince" name="province_code" required disabled>
                                    <option value="">Select Province</option>
                                </select>
                                <input type="hidden" id="editProvinceName" name="province_name">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="editCity">City/Municipality</label>
                                <select id="editCity" name="city_code" required disabled>
                                    <option value="">Select City</option>
                                </select>
                                <input type="hidden" id="editCityName" name="city_name">
                            </div>
                            <div class="form-group">
                                <label for="editBarangay">Barangay</label>
                                <select id="editBarangay" name="barangay_code" required disabled>
                                    <option value="">Select Barangay</option>
                                </select>
                                <input type="hidden" id="editBarangayName" name="barangay_name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="editStreet">Street Address</label>
                            <input type="text" id="editStreet" name="street">
                        </div>
                    </div>

                    <div class="section-title">Account Information</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editEmail">Email *</label>
                            <input type="email" id="editEmail" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="editStatus">Account Status</label>
                            <select id="editStatus" name="account_status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" id="cancelEditBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Status Confirmation Modal -->
    <div class="modal" id="statusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="statusModalTitle">Confirm Status Change</h2>
                <button class="modal-close" id="closeStatusModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="statusModalMessage"></p>
                <form id="statusForm" method="POST" action="clients.php">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="client_id" id="statusClientId">
                    <input type="hidden" name="account_status" id="statusValue">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelStatusBtn">Cancel</button>
                <button type="submit" form="statusForm" class="btn btn-primary" id="confirmStatusBtn">Confirm</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../javascript/register-address-selector.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Status filter buttons
            document.querySelectorAll('.status-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const status = btn.dataset.status;
                    window.location.href = `clients.php?status=${status}`;
                });
            });

            /* -- ADD CLIENT MODAL -- */
            const addClientBtn   = document.getElementById("addClientBtn");
            const addClientModal = document.getElementById("addClientModal");
            const closeAddModal   = document.getElementById("closeAddModal");
            const cancelAddBtn    = document.getElementById("cancelAddBtn");

            if (addClientBtn) {
                addClientBtn.addEventListener("click", () => {
                    document.getElementById("addClientForm").reset();
                    document.getElementById("birthdate").valueAsDate = new Date();
                    document.getElementById("account_status").value = "active";
                    addClientModal.classList.add("active");
                    
                    // Initialize the PH address selector for ADD modal
                    $('#region').on('change', my_handlers.fill_provinces);
                    $('#province').on('change', my_handlers.fill_cities);
                    $('#city').on('change', my_handlers.fill_barangays);
                    $('#barangay').on('change', my_handlers.onchange_barangay);
                });
            }

            if (closeAddModal) {
                closeAddModal.addEventListener("click", () => {
                    addClientModal.classList.remove("active");
                });
            }

            if (cancelAddBtn) {
                cancelAddBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    addClientModal.classList.remove("active");
                });
            }

            /* -- EDIT CLIENT MODAL -- */
            const editClientModal = document.getElementById("editClientModal");
            const closeEditModal   = document.getElementById("closeEditModal");
            const cancelEditBtn    = document.getElementById("cancelEditBtn");

            // Attach click event to all Edit buttons
            document.querySelectorAll(".edit-btn").forEach(button => {
                button.addEventListener("click", function () {
                    // Fill in the edit form fields
                    document.getElementById("editClientId").value = this.dataset.id;
                    document.getElementById("editFirstName").value = this.dataset.firstname;
                    document.getElementById("editLastName").value = this.dataset.lastname;
                    document.getElementById("editMiddleName").value = this.dataset.middlename || '';
                    document.getElementById("editBirthdate").value = this.dataset.birthdate;
                    document.getElementById("editContact").value = this.dataset.contact || '';
                    document.getElementById("editGender").value = this.dataset.gender;
                    document.getElementById("editEmail").value = this.dataset.email;
                    document.getElementById("editStatus").value = this.dataset.status;
                    document.getElementById("editStreet").value = this.dataset.street || '';

                    // Set address names in hidden fields
                    document.getElementById("editRegionName").value = this.dataset.region || '';
                    document.getElementById("editProvinceName").value = this.dataset.province || '';
                    document.getElementById("editCityName").value = this.dataset.city || '';
                    document.getElementById("editBarangayName").value = this.dataset.barangay || '';

                    // Initialize address selector for edit modal
                    initializeEditAddressSelector(this.dataset);

                    // Show modal
                    editClientModal.classList.add("active");
                });
            });

            if (closeEditModal) {
                closeEditModal.addEventListener("click", () => {
                    editClientModal.classList.remove("active");
                });
            }

            if (cancelEditBtn) {
                cancelEditBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    editClientModal.classList.remove("active");
                });
            }

            /* -- STATUS CHANGE MODAL -- */
            const statusModal = document.getElementById("statusModal");
            const closeStatusModal = document.getElementById("closeStatusModal");
            const cancelStatusBtn = document.getElementById("cancelStatusBtn");

            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('status-toggle-btn') || e.target.closest('.status-toggle-btn')) {
                    const btn = e.target.classList.contains('status-toggle-btn') ? e.target : e.target.closest('.status-toggle-btn');
                    const clientId = btn.dataset.id;
                    const clientName = btn.dataset.name;
                    const currentStatus = btn.dataset.status;
                    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                    const actionText = currentStatus === 'active' ? 'deactivate' : 'activate';
                    
                    document.getElementById('statusModalTitle').textContent = `Confirm ${actionText.charAt(0).toUpperCase() + actionText.slice(1)}`;
                    document.getElementById('statusModalMessage').textContent = `Are you sure you want to ${actionText} ${clientName}?`;
                    document.getElementById('statusClientId').value = clientId;
                    document.getElementById('statusValue').value = newStatus;
                    statusModal.classList.add('active');
                }
            });

            if (closeStatusModal) {
                closeStatusModal.addEventListener('click', () => {
                    statusModal.classList.remove('active');
                });
            }

            if (cancelStatusBtn) {
                cancelStatusBtn.addEventListener('click', () => {
                    statusModal.classList.remove('active');
                });
            }

            // Function to initialize address selector for edit modal
            function initializeEditAddressSelector(clientData) {
                $('#editRegion').empty().append('<option value="">Select Region</option>');
                $('#editProvince').empty().append('<option value="">Select Province</option>').prop('disabled', true);
                $('#editCity').empty().append('<option value="">Select City</option>').prop('disabled', true);
                $('#editBarangay').empty().append('<option value="">Select Barangay</option>').prop('disabled', true);

                const url = '../ph-json/region.json';
                $.getJSON(url, function(data) {
                    $.each(data, function(key, entry) {
                        $('#editRegion').append($('<option></option>').attr('value', entry.region_code).text(entry.region_name));
                    });
                    
                    if (clientData.region) {
                        const region = data.find(r => r.region_name === clientData.region);
                        if (region) {
                            $('#editRegion').val(region.region_code).trigger('change');
                            
                            setTimeout(() => {
                                if (clientData.province) {
                                    const provinceSelect = $('#editProvince');
                                    const provinceOption = provinceSelect.find('option').filter(function() {
                                        return $(this).text() === clientData.province;
                                    });
                                    if (provinceOption.length) {
                                        provinceSelect.val(provinceOption.val()).trigger('change');
                                        
                                        setTimeout(() => {
                                            if (clientData.city) {
                                                const citySelect = $('#editCity');
                                                const cityOption = citySelect.find('option').filter(function() {
                                                    return $(this).text() === clientData.city;
                                                });
                                                if (cityOption.length) {
                                                    citySelect.val(cityOption.val()).trigger('change');
                                                    
                                                    setTimeout(() => {
                                                        if (clientData.barangay) {
                                                            const barangaySelect = $('#editBarangay');
                                                            const barangayOption = barangaySelect.find('option').filter(function() {
                                                                return $(this).text() === clientData.barangay;
                                                            });
                                                            if (barangayOption.length) {
                                                                barangaySelect.val(barangayOption.val());
                                                            }
                                                        }
                                                    }, 500);
                                                }
                                            }
                                        }, 500);
                                    }
                                }
                            }, 500);
                        }
                    }
                });

                $('#editRegion').off('change').on('change', function() {
                    const regionCode = $(this).val();
                    const regionName = $(this).find('option:selected').text();
                    $('#editRegionName').val(regionName);
                    
                    $('#editProvince').empty().append('<option value="">Select Province</option>').prop('disabled', true);
                    $('#editCity').empty().append('<option value="">Select City</option>').prop('disabled', true);
                    $('#editBarangay').empty().append('<option value="">Select Barangay</option>').prop('disabled', true);
                    
                    if (regionCode) {
                        $.getJSON('../ph-json/province.json', function(data) {
                            const provinces = data.filter(p => p.region_code === regionCode);
                            provinces.sort((a, b) => a.province_name.localeCompare(b.province_name));
                            
                            provinces.forEach(province => {
                                $('#editProvince').append($('<option></option>').attr('value', province.province_code).text(province.province_name));
                            });
                            $('#editProvince').prop('disabled', false);
                        });
                    }
                });

                $('#editProvince').off('change').on('change', function() {
                    const provinceCode = $(this).val();
                    const provinceName = $(this).find('option:selected').text();
                    $('#editProvinceName').val(provinceName);
                    
                    $('#editCity').empty().append('<option value="">Select City</option>').prop('disabled', true);
                    $('#editBarangay').empty().append('<option value="">Select Barangay</option>').prop('disabled', true);
                    
                    if (provinceCode) {
                        $.getJSON('../ph-json/city.json', function(data) {
                            const cities = data.filter(c => c.province_code === provinceCode);
                            cities.sort((a, b) => a.city_name.localeCompare(b.city_name));
                            
                            cities.forEach(city => {
                                $('#editCity').append($('<option></option>').attr('value', city.city_code).text(city.city_name));
                            });
                            $('#editCity').prop('disabled', false);
                        });
                    }
                });

                $('#editCity').off('change').on('change', function() {
                    const cityCode = $(this).val();
                    const cityName = $(this).find('option:selected').text();
                    $('#editCityName').val(cityName);
                    
                    $('#editBarangay').empty().append('<option value="">Select Barangay</option>').prop('disabled', true);
                    
                    if (cityCode) {
                        $.getJSON('../ph-json/barangay.json', function(data) {
                            const barangays = data.filter(b => b.city_code === cityCode);
                            barangays.sort((a, b) => a.brgy_name.localeCompare(b.brgy_name));
                            
                            barangays.forEach(barangay => {
                                $('#editBarangay').append($('<option></option>').attr('value', barangay.brgy_code).text(barangay.brgy_name));
                            });
                            $('#editBarangay').prop('disabled', false);
                        });
                    }
                });

                $('#editBarangay').off('change').on('change', function() {
                    const barangayName = $(this).find('option:selected').text();
                    $('#editBarangayName').val(barangayName);
                });
            }

            // Password confirmation validation
            document.getElementById('confirmPassword').addEventListener('input', function() {
                const password = document.getElementById('password').value;
                const confirmPassword = this.value;
                const feedback = document.getElementById('passwordFeedback');
                
                if (confirmPassword.length > 0) {
                    if (password !== confirmPassword) {
                        feedback.innerHTML = '<span style="color:#e74c3c;">Passwords do not match</span>';
                        document.getElementById('saveClientBtn').disabled = true;
                    } else {
                        feedback.innerHTML = '<span style="color:#27ae60;">Passwords match</span>';
                        document.getElementById('saveClientBtn').disabled = false;
                    }
                } else {
                    feedback.innerHTML = '';
                }
            });
            
            // Email availability check
            document.getElementById('email').addEventListener('blur', function() {
                const email = this.value.trim();
                const feedback = document.getElementById('emailFeedback');
                
                if (email.length > 0) {
                    feedback.innerHTML = '<span style="color:#3498db;">Checking email availability...</span>';
                    
                    fetch('clients.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'check_email=1&email=' + encodeURIComponent(email)
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'exists') {
                            feedback.innerHTML = '<span style="color:#e74c3c;">Email already exists</span>';
                            document.getElementById('saveClientBtn').disabled = true;
                        } else {
                            feedback.innerHTML = '<span style="color:#27ae60;">Email is available</span>';
                            document.getElementById('saveClientBtn').disabled = false;
                        }
                    })
                    .catch(error => {
                        feedback.innerHTML = '<span style="color:#e74c3c;">Error checking email</span>';
                    });
                }
            });

            // Form validation for add client
            document.getElementById("addClientForm").addEventListener("submit", function(e) {
                const password = document.getElementById("password").value;
                const confirmPassword = document.getElementById("confirmPassword").value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "error",
                        title: "Password Mismatch",
                        text: "Please make sure your passwords match."
                    });
                    return false;
                }
                
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to add this client?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3498db',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Yes, add it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });

            // Form validation for edit client
            document.getElementById("editClientForm").addEventListener("submit", function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to update this client?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3498db',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });

            // Status form confirmation
            document.getElementById("statusForm").addEventListener("submit", function(e) {
                e.preventDefault();
                const status = document.getElementById('statusValue').value;
                const action = status === 'active' ? 'activate' : 'deactivate';
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to ${action} this client?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3498db',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: `Yes, ${action} it!`
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>

    <?php if (isset($_GET['success'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?= htmlspecialchars($_GET['success']) ?>',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.history.replaceState({}, document.title, "clients.php");
                });
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?= htmlspecialchars($_GET['error']) ?>',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.history.replaceState({}, document.title, "clients.php");
                });
            });
        </script>
    <?php endif; ?>
</body>
</html>