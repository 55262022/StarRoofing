<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

// Check if admin
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../../index.php');
    exit();
}

// Get order ID
if (!isset($_GET['order_id'])) {
    header('Location: ../order.php');
    exit();
}

$order_id = intval($_GET['order_id']);

// Fetch order details with assigned employee
$query = "
    SELECT o.*, 
           p.image_path,
           p.description as product_description,
           a.email as account_email,
           CONCAT(e.first_name, ' ', e.last_name) as assigned_employee_name
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.product_id
    LEFT JOIN accounts a ON o.account_id = a.id
    LEFT JOIN employees e ON o.assigned_employee_id = e.employee_id
    WHERE o.order_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: ../order.php');
    exit();
}

// Fetch order status history
$history_query = "
    SELECT osh.*, a.email as admin_email
    FROM order_status_history osh
    LEFT JOIN accounts a ON osh.created_by = a.id
    WHERE osh.order_id = ?
    ORDER BY osh.created_at DESC
";

$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $order_id);
$history_stmt->execute();
$history = $history_stmt->get_result();
$history_stmt->close();

// Get active employees for assignment
$emp_sql = "SELECT employee_id, first_name, last_name, department 
            FROM employees 
            WHERE is_archived = 0
            ORDER BY first_name, last_name";
$emp_result = $conn->query($emp_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Details #<?= htmlspecialchars($order['order_number']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Montserrat', sans-serif;
        background: #0a0a0a;
        color: #fff;
        min-height: 100vh;
        padding: 40px 20px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 800;
        color: #e9b949;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .back-btn {
        padding: 12px 24px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: #fff;
        text-decoration: none;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }

    .back-btn:hover {
        background: rgba(233,185,73,0.1);
        color: #e9b949;
    }

    .order-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 25px;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .card-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #e9b949;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .order-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: capitalize;
    }

    .status-pending { background: rgba(234, 179, 8, 0.2); color: #eab308; }
    .status-confirmed { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
    .status-processing { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
    .status-shipped { background: rgba(14, 165, 233, 0.2); color: #0ea5e9; }
    .status-delivered { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    .status-cancelled { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

    .product-section {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 20px;
        padding: 20px;
        background: rgba(255,255,255,0.02);
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .product-image {
        width: 150px;
        height: 150px;
        border-radius: 12px;
        overflow: hidden;
        background: rgba(255,255,255,0.05);
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-details {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .product-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #e9b949;
    }

    .product-meta {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        color: rgba(255,255,255,0.7);
    }

    .info-section {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: start;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 0.9rem;
        color: rgba(255,255,255,0.6);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-value {
        color: #fff;
        font-weight: 600;
        text-align: right;
    }

    .address-box {
        background: rgba(255,255,255,0.02);
        padding: 15px;
        border-radius: 10px;
        border-left: 3px solid #e9b949;
    }

    .total-section {
        background: rgba(233,185,73,0.1);
        padding: 20px;
        border-radius: 12px;
        margin-top: 20px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
    }

    .total-row.grand-total {
        font-size: 1.3rem;
        font-weight: 800;
        color: #e9b949;
        padding-top: 15px;
        border-top: 2px solid rgba(233,185,73,0.3);
        margin-top: 10px;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        padding: 15px 0;
        padding-left: 25px;
        border-left: 2px solid rgba(255,255,255,0.1);
    }

    .timeline-item:last-child {
        border-left-color: transparent;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 20px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #e9b949;
    }

    .timeline-status {
        font-weight: 700;
        color: #e9b949;
        margin-bottom: 5px;
        text-transform: capitalize;
    }

    .timeline-date {
        font-size: 0.85rem;
        color: rgba(255,255,255,0.6);
        margin-bottom: 5px;
    }

    .timeline-notes {
        font-size: 0.9rem;
        color: rgba(255,255,255,0.8);
        font-style: italic;
    }

    .timeline-admin {
        font-size: 0.85rem;
        color: rgba(255,255,255,0.5);
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 10px;
        border: none;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
    }

    .btn-update {
        background: rgba(168, 85, 247, 0.2);
        color: #a855f7;
        border: 1px solid rgba(168, 85, 247, 0.3);
    }

    .btn-update:hover {
        background: rgba(168, 85, 247, 0.3);
    }

    .btn-assign {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .btn-assign:hover {
        background: rgba(34, 197, 94, 0.3);
    }

    .btn-print {
        background: rgba(59, 130, 246, 0.2);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .btn-print:hover {
        background: rgba(59, 130, 246, 0.3);
    }

    .payment-proof {
        margin-top: 15px;
    }

    .payment-proof img {
        max-width: 100%;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .empty-history {
        text-align: center;
        padding: 40px;
        color: rgba(255,255,255,0.5);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: #1a1a2e;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        padding: 25px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #e9b949;
    }

    .modal-close {
        background: none;
        border: none;
        color: rgba(255,255,255,0.7);
        font-size: 2rem;
        cursor: pointer;
        padding: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: 0.3s;
    }

    .modal-close:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        padding: 20px 25px;
        border-top: 1px solid rgba(255,255,255,0.1);
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: rgba(255,255,255,0.9);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 10px;
        color: #fff;
        font-size: 1rem;
        font-family: 'Montserrat', sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #e9b949;
        box-shadow: 0 0 0 3px rgba(233,185,73,0.15);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid rgba(255,255,255,0.2);
        color: #fff;
    }

    .btn-outline:hover {
        background: rgba(255,255,255,0.05);
    }

    .btn-primary {
        background: #e9b949;
        color: #1a1a2e;
    }

    .btn-primary:hover {
        background: #d4a847;
    }

    @media (max-width: 1024px) {
        .order-grid {
            grid-template-columns: 1fr;
        }

        .product-section {
            grid-template-columns: 120px 1fr;
        }

        .product-image {
            width: 120px;
            height: 120px;
        }
    }

    @media (max-width: 768px) {
        .product-section {
            grid-template-columns: 1fr;
        }

        .product-meta {
            grid-template-columns: 1fr;
        }

        .info-row {
            flex-direction: column;
            gap: 5px;
        }

        .info-value {
            text-align: left;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media print {
        body {
            background: #fff;
            color: #000;
        }

        .back-btn, .action-buttons, .modal {
            display: none !important;
        }

        .card {
            border: 1px solid #ddd;
            background: #fff;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-file-invoice"></i> Order #<?= htmlspecialchars($order['order_number']) ?>
            </h1>
            <a href="../order.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>

        <div class="order-grid">
            <!-- Left Column - Order Details -->
            <div>
                <!-- Order Status Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-info-circle"></i> Order Information
                        </h2>
                        <div class="order-status status-<?= $order['order_status'] ?>">
                            <?= ucfirst($order['order_status']) ?>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-hashtag"></i> Order Number
                            </span>
                            <span class="info-value"><?= htmlspecialchars($order['order_number']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-calendar"></i> Order Date
                            </span>
                            <span class="info-value"><?= date('F j, Y - g:i A', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-credit-card"></i> Payment Method
                            </span>
                            <span class="info-value"><?= strtoupper($order['payment_method']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-money-check"></i> Payment Status
                            </span>
                            <span class="info-value"><?= ucfirst($order['payment_status']) ?></span>
                        </div>
                        <?php if (!empty($order['assigned_employee_name'])): ?>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-user-check"></i> Assigned Employee
                            </span>
                            <span class="info-value" style="color: #22c55e;"><?= htmlspecialchars($order['assigned_employee_name']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($order['payment_proof']): ?>
                    <div class="payment-proof">
                        <strong>Payment Proof:</strong>
                        <br><br>
                        <img src="../../uploads/payment_proofs/<?= htmlspecialchars($order['payment_proof']) ?>" 
                             alt="Payment Proof">
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Details Card -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-box"></i> Product Details
                        </h2>
                    </div>

                    <div class="product-section">
                        <div class="product-image">
                            <img src="../../<?= htmlspecialchars($order['image_path'] ?? 'no-image.png') ?>" 
                                 alt="Product">
                        </div>
                        <div class="product-details">
                            <div class="product-name"><?= htmlspecialchars($order['product_name']) ?></div>
                            <?php if ($order['product_description']): ?>
                            <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                <?= htmlspecialchars($order['product_description']) ?>
                            </p>
                            <?php endif; ?>
                            <div class="product-meta">
                                <div>
                                    <strong>Price:</strong> ₱<?= number_format($order['product_price'], 2) ?>
                                </div>
                                <div>
                                    <strong>Quantity:</strong> <?= $order['quantity'] ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="total-section">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>₱<?= number_format($order['subtotal'], 2) ?></span>
                        </div>
                        <div class="total-row">
                            <span>Delivery Fee:</span>
                            <span>₱<?= number_format($order['delivery_fee'], 2) ?></span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total Amount:</span>
                            <span>₱<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Customer Details Card -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-user"></i> Customer Information
                        </h2>
                    </div>

                    <div class="info-section">
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-user-circle"></i> Name
                            </span>
                            <span class="info-value">
                                <?= htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']) ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-envelope"></i> Email
                            </span>
                            <span class="info-value"><?= htmlspecialchars($order['customer_email']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-phone"></i> Phone
                            </span>
                            <span class="info-value"><?= htmlspecialchars($order['customer_phone']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                <i class="fas fa-user-tag"></i> Account
                            </span>
                            <span class="info-value"><?= htmlspecialchars($order['account_email']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address Card -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-map-marker-alt"></i> Delivery Address
                        </h2>
                    </div>

                    <div class="address-box">
                        <p style="margin-bottom: 8px;">
                            <strong><?= htmlspecialchars($order['delivery_street'] ?? 'N/A') ?></strong>
                        </p>
                        <p style="color: rgba(255,255,255,0.8);">
                            <?= htmlspecialchars($order['delivery_barangay']) ?>,
                            <?= htmlspecialchars($order['delivery_city']) ?>
                        </p>
                        <p style="color: rgba(255,255,255,0.8);">
                            <?= htmlspecialchars($order['delivery_province']) ?>,
                            <?= htmlspecialchars($order['delivery_region']) ?>
                        </p>
                        <?php if ($order['delivery_notes']): ?>
                        <p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.7); font-style: italic;">
                            <strong>Notes:</strong> <?= htmlspecialchars($order['delivery_notes']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Status History & Actions -->
            <div>
                <!-- Action Buttons Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-cogs"></i> Actions
                        </h2>
                    </div>

                    <div class="action-buttons">
                        <?php if ($order['order_status'] !== 'cancelled' && $order['order_status'] !== 'delivered'): ?>
                        <button class="btn btn-update" onclick="updateOrderStatus(<?= $order['order_id'] ?>, '<?= $order['order_status'] ?>')">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($order['order_status'] === 'processing' || $order['order_status'] === 'confirmed'): ?>
                        <button class="btn btn-assign" onclick="openAssignModal()">
                            <i class="fas fa-user-plus"></i> 
                            <?= empty($order['assigned_employee_name']) ? 'Assign Employee' : 'Reassign Employee' ?>
                        </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-print" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Order
                        </button>
                    </div>
                </div>

                <!-- Status History Card -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-history"></i> Status History
                        </h2>
                    </div>

                    <?php if ($history->num_rows > 0): ?>
                    <div class="timeline">
                        <?php while ($h = $history->fetch_assoc()): ?>
                        <div class="timeline-item">
                            <div class="timeline-status"><?= ucfirst($h['status']) ?></div>
                            <div class="timeline-date">
                                <?= date('M j, Y - g:i A', strtotime($h['created_at'])) ?>
                            </div>
                            <?php if ($h['notes']): ?>
                            <div class="timeline-notes"><?= htmlspecialchars($h['notes']) ?></div>
                            <?php endif; ?>
                            <?php if ($h['admin_email']): ?>
                            <div class="timeline-admin">by <?= htmlspecialchars($h['admin_email']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-history">
                        <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; margin-bottom: 10px;"></i>
                        <p>No status history available</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Timestamps Card -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-clock"></i> Timestamps
                        </h2>
                    </div>

                    <div class="info-section">
                        <div class="info-row">
                            <span class="info-label">Created</span>
                            <span class="info-value">
                                <?= date('M j, Y - g:i A', strtotime($order['created_at'])) ?>
                            </span>
                        </div>
                        <?php if ($order['confirmed_at']): ?>
                        <div class="info-row">
                            <span class="info-label">Confirmed</span>
                            <span class="info-value">
                                <?= date('M j, Y - g:i A', strtotime($order['confirmed_at'])) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php if ($order['shipped_at']): ?>
                        <div class="info-row">
                            <span class="info-label">Shipped</span>
                            <span class="info-value">
                                <?= date('M j, Y - g:i A', strtotime($order['shipped_at'])) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php if ($order['delivered_at']): ?>
                        <div class="info-row">
                            <span class="info-label">Delivered</span>
                            <span class="info-value">
                                <?= date('M j, Y - g:i A', strtotime($order['delivered_at'])) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="info-row">
                            <span class="info-label">Last Updated</span>
                            <span class="info-value">
                                <?= date('M j, Y - g:i A', strtotime($order['updated_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Employee Modal -->
    <div class="modal" id="assignEmployeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Assign Delivery Employee</h2>
                <button class="modal-close" onclick="closeAssignModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="assignEmployeeForm">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    
                    <div class="form-group">
                        <label>Order Number</label>
                        <input type="text" value="#<?= htmlspecialchars($order['order_number']) ?>" readonly style="background: rgba(255,255,255,0.03);">
                    </div>

                    <div class="form-group">
                        <label>Customer Name</label>
                        <input type="text" value="<?= htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']) ?>" readonly style="background: rgba(255,255,255,0.03);">
                    </div>
                    
                    <div class="form-group">
                        <label for="employee_id">Select Delivery Employee *</label>
                        <select id="employee_id" name="employee_id" required>
                            <option value="">-- Select Employee --</option>
                            <?php
                            $emp_result->data_seek(0); // Reset result pointer
                            while ($emp = $emp_result->fetch_assoc()): 
                            ?>
                                <option value="<?= $emp['employee_id'] ?>" <?= ($order['assigned_employee_id'] == $emp['employee_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' - ' . $emp['department']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">Delivery Instructions (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Add any special delivery instructions..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeAssignModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAssignment()">Assign Employee</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function openAssignModal() {
        document.getElementById('assignEmployeeModal').classList.add('active');
    }

    function closeAssignModal() {
        document.getElementById('assignEmployeeModal').classList.remove('active');
        document.getElementById('assignEmployeeForm').reset();
    }

    function submitAssignment() {
        const form = document.getElementById('assignEmployeeForm');
        const formData = new FormData(form);
        
        if (!formData.get('employee_id')) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select an employee',
                confirmButtonColor: '#e9b949'
            });
            return;
        }
        
        fetch('assign_employee.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Employee Assigned!',
                    text: data.message || 'Employee has been assigned to this order.',
                    confirmButtonColor: '#e9b949'
                }).then(() => {
                    closeAssignModal();
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to assign employee.',
                    confirmButtonColor: '#e9b949'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while assigning the employee.',
                confirmButtonColor: '#e9b949'
            });
        });
    }

    function updateOrderStatus(orderId, currentStatus) {
        const statusOptions = {
            'pending': ['confirmed', 'cancelled'],
            'confirmed': ['processing', 'cancelled'],
            'processing': ['shipped', 'cancelled'],
            'shipped': ['delivered'],
            'delivered': [],
            'cancelled': []
        };

        const nextStatuses = statusOptions[currentStatus];
        
        if (nextStatuses.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No Actions Available',
                text: 'This order cannot be updated further.',
                confirmButtonColor: '#e9b949'
            });
            return;
        }

        const inputOptions = {};
        nextStatuses.forEach(status => {
            inputOptions[status] = status.charAt(0).toUpperCase() + status.slice(1);
        });

        Swal.fire({
            title: 'Update Order Status',
            input: 'select',
            inputOptions: inputOptions,
            inputPlaceholder: 'Select new status',
            showCancelButton: true,
            confirmButtonColor: '#e9b949',
            confirmButtonText: 'Update',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please select a status!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateStatus(orderId, result.value);
            }
        });
    }

    function updateStatus(orderId, newStatus) {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', newStatus);
        
        fetch('update_order_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Order status has been updated.',
                    confirmButtonColor: '#e9b949'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to update order status.',
                    confirmButtonColor: '#e9b949'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the order.',
                confirmButtonColor: '#e9b949'
            });
        });
    }

    // Close modal when clicking outside
    document.getElementById('assignEmployeeModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAssignModal();
        }
    });
    </script>
</body>
</html>