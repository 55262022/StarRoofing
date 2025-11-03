<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Initialize variables
$status_filter = $_GET['status'] ?? 'all';
$search_term = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query for clients
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

// Handle ban/unban action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ban_account') {
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
            $action_text = $new_status === 'suspended' ? 'banned' : 'unbanned';
            $_SESSION['message'] = "Client account has been {$action_text} successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating account status: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        
        header("Location: clients.php");
        exit();
    }
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

        .btn-warning {
            background-color: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #229954;
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

        .status.suspended {
            background-color: #fff3cd;
            color: #856404;
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
            max-width: 500px;
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
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
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
    </style>
</head>
<body>
    <div class="main-container">
        <div class="main-content">
            <div class="client-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Clients Information</h1>
                        <p class="page-description">View and manage client accounts</p>
                    </div>
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
                    <button class="status-btn <?= $status_filter === 'suspended' ? 'active' : '' ?>" data-status="suspended">Banned</button>
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
                                                    <?= $client['account_status'] === 'suspended' ? 'Banned' : ucfirst($client['account_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($client['account_status'] !== 'suspended'): ?>
                                                    <button class="btn btn-danger ban-btn" 
                                                            data-id="<?= $client['id'] ?>" 
                                                            data-name="<?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>">
                                                        <i class="fas fa-ban"></i> Ban Account
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-success unban-btn" 
                                                            data-id="<?= $client['id'] ?>" 
                                                            data-name="<?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>">
                                                        <i class="fas fa-check-circle"></i> Unban Account
                                                    </button>
                                                <?php endif; ?>
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
                                <p>No clients found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ban Confirmation Modal -->
    <div class="modal" id="banModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="banModalTitle">Confirm Ban Account</h2>
                <button class="modal-close" id="closeBanModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="banModalMessage"></p>
                <form id="banForm" method="POST" action="clients.php">
                    <input type="hidden" name="action" value="ban_account">
                    <input type="hidden" name="client_id" id="banClientId">
                    <input type="hidden" name="account_status" id="banStatusValue">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelBanBtn">Cancel</button>
                <button type="submit" form="banForm" class="btn btn-danger" id="confirmBanBtn">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Status filter buttons
            document.querySelectorAll('.status-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const status = btn.dataset.status;
                    window.location.href = `clients.php?status=${status}`;
                });
            });

            /* -- BAN/UNBAN MODAL -- */
            const banModal = document.getElementById("banModal");
            const closeBanModal = document.getElementById("closeBanModal");
            const cancelBanBtn = document.getElementById("cancelBanBtn");

            // Ban Account
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('ban-btn') || e.target.closest('.ban-btn')) {
                    const btn = e.target.classList.contains('ban-btn') ? e.target : e.target.closest('.ban-btn');
                    const clientId = btn.dataset.id;
                    const clientName = btn.dataset.name;
                    
                    document.getElementById('banModalTitle').textContent = 'Confirm Ban Account';
                    document.getElementById('banModalMessage').textContent = `Are you sure you want to ban ${clientName}? This will suspend their account and prevent them from accessing the system.`;
                    document.getElementById('banClientId').value = clientId;
                    document.getElementById('banStatusValue').value = 'suspended';
                    document.getElementById('confirmBanBtn').textContent = 'Ban Account';
                    document.getElementById('confirmBanBtn').className = 'btn btn-danger';
                    banModal.classList.add('active');
                }

                // Unban Account
                if (e.target.classList.contains('unban-btn') || e.target.closest('.unban-btn')) {
                    const btn = e.target.classList.contains('unban-btn') ? e.target : e.target.closest('.unban-btn');
                    const clientId = btn.dataset.id;
                    const clientName = btn.dataset.name;
                    
                    document.getElementById('banModalTitle').textContent = 'Confirm Unban Account';
                    document.getElementById('banModalMessage').textContent = `Are you sure you want to unban ${clientName}? This will reactivate their account.`;
                    document.getElementById('banClientId').value = clientId;
                    document.getElementById('banStatusValue').value = 'active';
                    document.getElementById('confirmBanBtn').textContent = 'Unban Account';
                    document.getElementById('confirmBanBtn').className = 'btn btn-success';
                    banModal.classList.add('active');
                }
            });

            if (closeBanModal) {
                closeBanModal.addEventListener('click', () => {
                    banModal.classList.remove('active');
                });
            }

            if (cancelBanBtn) {
                cancelBanBtn.addEventListener('click', () => {
                    banModal.classList.remove('active');
                });
            }

            // Ban form confirmation
            document.getElementById("banForm").addEventListener("submit", function(e) {
                e.preventDefault();
                const status = document.getElementById('banStatusValue').value;
                const action = status === 'suspended' ? 'ban' : 'unban';
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to ${action} this account?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: status === 'suspended' ? '#e74c3c' : '#27ae60',
                    cancelButtonColor: '#95a5a6',
                    confirmButtonText: `Yes, ${action} it!`
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    } else {
                        banModal.classList.remove('active');
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