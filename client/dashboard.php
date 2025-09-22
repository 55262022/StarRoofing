<?php
// include '../includes/auth.php';
require_once '../database/starroofing_db.php';

// Get account_id from session
// $account_id = $_SESSION['account_id'];

// Fetch profile info from user_profiles
// $profile_query = $conn->prepare("SELECT full_name FROM user_profiles WHERE account_id = ?");
// $profile_query->bind_param("i", $account_id);
// $profile_query->execute();
// $profile_result = $profile_query->get_result();
// $profile = $profile_result->fetch_assoc();

// Fetch email from accounts
$account_query = $conn->prepare("SELECT email FROM accounts WHERE id = ?");
$account_query->bind_param("i", $account_id);
$account_query->execute();
$account_result = $account_query->get_result();
$account = $account_result->fetch_assoc();

$full_name = $profile['full_name'] ?? 'Client';
$email = $account['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background: #f5f7f9;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 0;
            display: flex;
            flex-direction: column;
        }
        .dashboard-content {
            padding: 32px;
        }
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a365d;
            margin-bottom: 24px;
        }
        @media (max-width: 900px) {
            .dashboard-container { flex-direction: column; }
            .sidebar { width: 100%; min-height: unset; }
            .main-content { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include '../includes/client_sidebar.php'; ?>
        <!-- Main Content -->
        <div class="main-content">
            <!-- Navbar -->
            <!-- Top Navigation -->
             <?php include '../includes/client_navbar.php'; ?>
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <div class="page-title">Welcome, <?= htmlspecialchars($full_name) ?>!</div>
                <p>Here you can view your projects, update your profile, and more.</p>
                <!-- Add more client dashboard widgets/content here -->
            </div>
        </div>
    </div>
</body>
</html>