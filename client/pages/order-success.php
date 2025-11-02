<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

// Get order number from URL
$order_number = $_GET['order_number'] ?? '';

if (empty($order_number)) {
    header('Location: ../materials.php');
    exit();
}

// Get order details
$query = "
    SELECT o.*, p.image_path, p.category_id, c.category_name
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.product_id
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE o.order_number = ? AND o.account_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $order_number, $_SESSION['account_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: ../materials.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmed - Star Roofing & Construction</title>
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
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .success-container {
        max-width: 800px;
        width: 100%;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 24px;
        padding: 40px;
        backdrop-filter: blur(10px);
        animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .success-icon {
        text-align: center;
        margin-bottom: 30px;
    }

    .success-icon i {
        font-size: 5rem;
        color: #10b981;
        animation: scaleIn 0.5s ease;
    }

    @keyframes scaleIn {
        from {
            transform: scale(0);
        }
        to {
            transform: scale(1);
        }
    }

    .success-title {
        text-align: center;
        font-size: 2rem;
        font-weight: 800;
        color: #e9b949;
        margin-bottom: 10px;
    }

    .success-message {
        text-align: center;
        color: rgba(255,255,255,0.7);
        margin-bottom: 40px;
    }

    .order-number-box {
        background: rgba(233,185,73,0.1);
        border: 2px solid #e9b949;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        margin-bottom: 30px;
    }

    .order-number-label {
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .order-number {
        font-size: 2rem;
        font-weight: 800;
        color: #e9b949;
        letter-spacing: 2px;
    }

    .order-details {
        background: rgba(255,255,255,0.05);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
    }

    .detail-section {
        margin-bottom: 25px;
    }

    .detail-section:last-child {
        margin-bottom: 0;
    }

    .detail-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #e9b949;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: rgba(255,255,255,0.6);
    }

    .detail-value {
        color: #fff;
        font-weight: 600;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        margin-top: 15px;
        border-top: 2px solid rgba(233,185,73,0.3);
        font-size: 1.2rem;
        font-weight: 700;
        color: #e9b949;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn {
        padding: 15px 30px;
        border-radius: 12px;
        border: none;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-primary {
        background: #e9b949;
        color: #1a1a2e;
    }

    .btn-primary:hover {
        background: transparent;
        border: 2px solid #e9b949;
        color: #e9b949;
        box-shadow: 0 0 25px rgba(233,185,73,0.4);
        transform: translateY(-3px);
    }

    .btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border: 2px solid rgba(255,255,255,0.2);
    }

    .btn-secondary:hover {
        background: rgba(255,255,255,0.15);
        transform: translateY(-3px);
    }

    .info-note {
        background: rgba(16, 185, 129, 0.1);
        border-left: 3px solid #10b981;
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .info-note i {
        color: #10b981;
        margin-right: 10px;
    }

    .info-note p {
        color: rgba(255,255,255,0.8);
        font-size: 0.9rem;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .success-container {
            padding: 30px 20px;
        }

        .success-title {
            font-size: 1.5rem;
        }

        .order-number {
            font-size: 1.5rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>

        <h1 class="success-title">Order Placed Successfully!</h1>
        <p class="success-message">Thank you for your order. We'll process it shortly.</p>

        <div class="order-number-box">
            <div class="order-number-label">Your Order Number</div>
            <div class="order-number"><?= htmlspecialchars($order['order_number']) ?></div>
        </div>

        <div class="order-details">
            <div class="detail-section">
                <div class="detail-title">
                    <i class="fas fa-box"></i> Order Information
                </div>
                <div class="detail-row">
                    <span class="detail-label">Product:</span>
                    <span class="detail-value"><?= htmlspecialchars($order['product_name']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Quantity:</span>
                    <span class="detail-value"><?= $order['quantity'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value"><?= date('F j, Y - g:i A', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: #e9b949;">Pending</span>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-title">
                    <i class="fas fa-map-marker-alt"></i> Delivery Address
                </div>
                <div class="detail-row">
                    <span class="detail-value">
                        <?php 
                        $address_parts = array_filter([
                            $order['delivery_street'],
                            $order['delivery_barangay'],
                            $order['delivery_city'],
                            $order['delivery_province']
                        ]);
                        echo htmlspecialchars(implode(', ', $address_parts));
                        ?>
                    </span>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-title">
                    <i class="fas fa-credit-card"></i> Payment Details
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">
                        <?php
                        $payment_methods = [
                            'cod' => 'Cash on Delivery',
                            'gcash' => 'GCash',
                            'bank' => 'Bank Transfer'
                        ];
                        echo $payment_methods[$order['payment_method']] ?? 'N/A';
                        ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Subtotal:</span>
                    <span class="detail-value">₱<?= number_format($order['subtotal'], 2) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Delivery Fee:</span>
                    <span class="detail-value">₱<?= number_format($order['delivery_fee'], 2) ?></span>
                </div>
                <div class="total-row">
                    <span>Total Amount:</span>
                    <span>₱<?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <?php if ($order['payment_method'] !== 'cod'): ?>
        <div class="info-note">
            <i class="fas fa-info-circle"></i>
            <p>
                <strong>Next Steps:</strong> Please complete your payment using the <?= $payment_methods[$order['payment_method']] ?> details provided during checkout. 
                Once payment is confirmed, we will process your order immediately.
            </p>
        </div>
        <?php endif; ?>

        <div class="action-buttons">
            <!-- track order button -->
            <a href="track-order.php?order_number=<?= urlencode($order['order_number']) ?>" class="btn btn-primary">
                <i class="fas fa-search"></i> Track Order
            </a>
            <a href="../materials.php" class="btn btn-secondary">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>