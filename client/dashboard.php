<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Get account_id from session
$account_id = $_SESSION['account_id'];

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
        .hidden{
            display: none;
            
        }
        .sidebar-menu li a.active {
            background-color: #1a365d;
            color: #ffffff;
        }

        .sidebar-menu li a.active i {
            color: #ffffff;
        }

        .sidebar-menu li a:hover {
            background-color: #2a4c8a;
            color: #ffffff;
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
    <!-- <h1><b>lalagyan ng dedicated messages page</b></h1> -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include '../includes/client_sidebar.php'; ?>
        <!-- Main Content -->
        <main class="main-content">
            <!-- Navbar -->
            <!-- Top Navigation -->
             <?php include '../includes/client_navbar.php'; ?>
            <!-- Dashboard Content -->
            <section class="dashboard-content">
                <div class="page-title">Welcome, <?= htmlspecialchars($full_name) ?>!</div>
                <p>Here you can view your projects, update your profile, and more.</p>
                <!-- Add more client dashboard widgets/content here -->
            </section>
            <!-- products page -->
            <section id="materials-section" class="section hidden">
                <iframe src="materials.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
            <!-- inquiry page -->
            <section id="inquiry-section" class="section hidden">
                <iframe src="pages/inquiry.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
            <!-- chats page -->
            <section id="chats-section" class="section hidden">
                <iframe src="pages/client-messages.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
        </main>
    </div>
</body>
</html>