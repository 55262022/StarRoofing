<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

requireEmployee();

// Get employee_id - handle both session-based and account-based employee login
$employee_id = null;
$employee_name = getCurrentUserName();

// Check if employee_id exists in session
if (isset($_SESSION['employee_id'])) {
    $employee_id = $_SESSION['employee_id'];
} else {
    // If not in session, try to get it from database using account_id
    if (isset($_SESSION['account_id'])) {
        $account_id = $_SESSION['account_id'];
        $emp_query = "SELECT employee_id, first_name, last_name, department 
                      FROM employees 
                      WHERE account_id = ? AND is_archived = 0";
        $emp_stmt = $conn->prepare($emp_query);
        $emp_stmt->bind_param("i", $account_id);
        $emp_stmt->execute();
        $emp_result = $emp_stmt->get_result();
        
        if ($emp_result->num_rows > 0) {
            $emp_data = $emp_result->fetch_assoc();
            $employee_id = $emp_data['employee_id'];
            // Set it in session for future use
            $_SESSION['employee_id'] = $employee_id;
            $_SESSION['first_name'] = $emp_data['first_name'];
            $_SESSION['last_name'] = $emp_data['last_name'];
            $_SESSION['department'] = $emp_data['department'];
            $employee_name = $emp_data['first_name'] . ' ' . $emp_data['last_name'];
        }
        $emp_stmt->close();
    }
}

// If still no employee_id found, redirect to login
if (!$employee_id) {
    $_SESSION['error'] = "Employee record not found. Please contact administrator.";
    header("Location: ../public/login.php");
    exit();
}

// Get employee's assigned orders - INCLUDING PROCESSING STATUS
$sql = "SELECT 
    o.order_id,
    o.order_number,
    o.customer_first_name,
    o.customer_last_name,
    o.customer_phone,
    o.delivery_street,
    o.delivery_barangay,
    o.delivery_city,
    o.delivery_province,
    o.delivery_region,
    o.delivery_notes,
    o.product_name,
    o.quantity,
    o.order_status,
    o.delivery_proof,
    o.shipped_at,
    o.delivered_at,
    o.created_at
FROM orders o
WHERE o.assigned_employee_id = ? 
AND o.order_status IN ('processing', 'shipped', 'delivered')
ORDER BY 
    CASE 
        WHEN o.order_status = 'processing' THEN 1
        WHEN o.order_status = 'shipped' THEN 2
        WHEN o.order_status = 'delivered' THEN 3
    END,
    o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$deliveries = [];
while ($row = $result->fetch_assoc()) {
    $deliveries[] = $row;
}

// Calculate statistics
$pending_count = 0;
$active_count = 0;
$completed_today = 0;
$total_count = count($deliveries);

$today = date('Y-m-d');

foreach ($deliveries as $delivery) {
    if ($delivery['order_status'] === 'processing') {
        $pending_count++;
    }
    if ($delivery['order_status'] === 'shipped') {
        $active_count++;
    }
    if ($delivery['order_status'] === 'delivered' && 
        date('Y-m-d', strtotime($delivery['delivered_at'])) === $today) {
        $completed_today++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Portal - Logistics & Delivery</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            color: #fff;
        }

        .header {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #e9b949, #d4a437);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .logo-text h1 {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .logo-text p {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .user-info span {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .logout-btn {
            padding: 0.5rem 1rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 1.5rem;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.blue { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .stat-icon.green { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
        .stat-icon.gold { background: rgba(233, 185, 73, 0.2); color: #e9b949; }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.7);
        }

        .filter-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .filter-btn.active {
            background: #e9b949;
            color: #1a1a2e;
        }

        .filter-count {
            display: inline-block;
            margin-left: 0.5rem;
            padding: 0.15rem 0.5rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            font-size: 0.75rem;
        }

        .deliveries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
            gap: 1.5rem;
        }

        .delivery-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .delivery-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }

        .delivery-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .delivery-id {
            color: #e9b949;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .client-name {
            font-size: 1.125rem;
            font-weight: 700;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-badge.shipped {
            background: #3b82f6;
            color: #fff;
        }

        .status-badge.delivered {
            background: #22c55e;
            color: #fff;
        }

        .delivery-details {
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .detail-icon {
            color: #e9b949;
            margin-top: 0.25rem;
            flex-shrink: 0;
        }

        .detail-content p:first-child {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 0.25rem;
        }

        .detail-content p:last-child {
            font-size: 0.875rem;
            color: #fff;
        }

        .delivery-notes {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 1rem;
        }

        .delivery-notes p:first-child {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 0.25rem;
        }

        .delivery-notes p:last-child {
            font-size: 0.875rem;
            color: #fff;
        }

        .delivery-actions {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .upload-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: #e9b949;
            color: #1a1a2e;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-btn:hover {
            background: #d4a437;
            transform: translateY(-2px);
        }

        .proof-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 0.75rem;
        }

        .completed-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #22c55e;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
            color: rgba(255, 255, 255, 0.4);
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: rgba(255, 255, 255, 0.6);
        }

        .hidden {
            display: none !important;
        }

        @media (max-width: 768px) {
            .header-content {
                padding: 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .deliveries-grid {
                grid-template-columns: 1fr;
            }

            .user-info span {
                display: none;
            }

            .filter-tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-icon"><i class="fas fa-truck"></i></div>
                <div class="logo-text">
                    <h1>Employee Portal</h1>
                    <p>Logistics & Delivery</p>
                </div>
            </div>
            
            <div class="header-actions">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?= htmlspecialchars($employee_name) ?></span>
                </div>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Active Deliveries</span>
                    <div class="stat-icon blue"><i class="fas fa-shipping-fast"></i></div>
                </div>
                <div class="stat-value"><?= $active_count ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Completed Today</span>
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-value"><?= $completed_today ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Assigned</span>
                    <div class="stat-icon gold"><i class="fas fa-box"></i></div>
                </div>
                <div class="stat-value"><?= $total_count ?></div>
            </div>
        </div>

        <div class="filter-tabs">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="shipped">
                Shipped <span class="filter-count"><?= $active_count ?></span>
            </button>
            <button class="filter-btn" data-filter="delivered">
                Delivered <span class="filter-count"><?= $total_count - $active_count ?></span>
            </button>
        </div>

        <div class="deliveries-grid" id="deliveriesContainer">
            <?php if (count($deliveries) > 0): ?>
                <?php foreach ($deliveries as $delivery): ?>
                    <div class="delivery-card" data-status="<?= htmlspecialchars($delivery['order_status']) ?>">
                        <div class="delivery-header">
                            <div>
                                <div class="delivery-id">#<?= htmlspecialchars($delivery['order_number']) ?></div>
                                <div class="client-name">
                                    <?= htmlspecialchars($delivery['customer_first_name'] . ' ' . $delivery['customer_last_name']) ?>
                                </div>
                            </div>
                            <span class="status-badge <?= htmlspecialchars($delivery['order_status']) ?>">
                                <i class="fas fa-<?= $delivery['order_status'] === 'shipped' ? 'truck' : 'check-circle' ?>"></i>
                                <?= ucfirst($delivery['order_status']) ?>
                            </span>
                        </div>

                        <div class="delivery-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt detail-icon"></i>
                                <div class="detail-content">
                                    <p>Delivery Address</p>
                                    <p>
                                        <?= htmlspecialchars($delivery['delivery_street']) ?>, 
                                        <?= htmlspecialchars($delivery['delivery_barangay']) ?>, 
                                        <?= htmlspecialchars($delivery['delivery_city']) ?>, 
                                        <?= htmlspecialchars($delivery['delivery_province']) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="fas fa-phone detail-icon"></i>
                                <div class="detail-content">
                                    <p>Contact Number</p>
                                    <p><?= htmlspecialchars($delivery['customer_phone']) ?></p>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="fas fa-box detail-icon"></i>
                                <div class="detail-content">
                                    <p>Product</p>
                                    <p><?= htmlspecialchars($delivery['product_name']) ?> (Qty: <?= $delivery['quantity'] ?>)</p>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="fas fa-clock detail-icon"></i>
                                <div class="detail-content">
                                    <p>Shipped Date</p>
                                    <p><?= date('M j, Y g:i A', strtotime($delivery['shipped_at'])) ?></p>
                                </div>
                            </div>

                            <?php if (!empty($delivery['delivery_notes'])): ?>
                                <div class="delivery-notes">
                                    <p>Delivery Notes</p>
                                    <p><?= htmlspecialchars($delivery['delivery_notes']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($delivery['order_status'] === 'shipped'): ?>
                            <div class="delivery-actions">
                                <form action="../employee/upload_delivery_proof.php" method="POST" enctype="multipart/form-data" class="upload-form">
                                    <input type="hidden" name="order_id" value="<?= $delivery['order_id'] ?>">
                                    <input type="file" name="proof_image" id="proof-<?= $delivery['order_id'] ?>" accept="image/*" style="display: none;" required>
                                    <button type="button" class="upload-btn" onclick="document.getElementById('proof-<?= $delivery['order_id'] ?>').click()">
                                        <i class="fas fa-camera"></i> Upload Proof & Mark Delivered
                                    </button>
                                </form>
                            </div>
                        <?php elseif ($delivery['order_status'] === 'delivered' && !empty($delivery['delivery_proof'])): ?>
                            <div class="delivery-actions">
                                <p style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 0.5rem;">Proof of Delivery</p>
                                <img src="../<?= htmlspecialchars($delivery['delivery_proof']) ?>" alt="Proof of delivery" class="proof-image">
                                <div class="completed-badge">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Delivery Completed - <?= date('M j, Y', strtotime($delivery['delivered_at'])) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                    <h3>No Deliveries Assigned</h3>
                    <p>You don't have any deliveries assigned at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                const cards = document.querySelectorAll('.delivery-card');
                
                cards.forEach(card => {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Handle file input change to auto-submit
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const form = this.closest('form');
                    
                    Swal.fire({
                        title: 'Confirm Delivery',
                        text: 'Are you sure you want to mark this order as delivered?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#22c55e',
                        cancelButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, mark as delivered'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        } else {
                            input.value = '';
                        }
                    });
                }
            });
        });

        // Logout function
        function logout() {
            Swal.fire({
                title: 'Logout',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../public/logout.php';
                }
            });
        }

        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= addslashes($_SESSION['success']) ?>',
                timer: 3000,
                showConfirmButton: false
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= addslashes($_SESSION['error']) ?>',
                timer: 3000,
                showConfirmButton: false
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>