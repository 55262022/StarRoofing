<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

$order = null;
$order_history = [];

if (isset($_GET['order_number'])) {
    $order_number = trim($_GET['order_number']);
    
    // Get order details
    $query = "
        SELECT o.*, p.image_path, c.category_name
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
    
    if ($order) {
        // Get status history
        $history_query = "SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC";
        $history_stmt = $conn->prepare($history_query);
        $history_stmt->bind_param("i", $order['order_id']);
        $history_stmt->execute();
        $order_history = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $history_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track Order - Star Roofing & Construction</title>
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
        max-width: 1000px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 800;
        color: #e9b949;
    }

    .back-btn {
        padding: 10px 20px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: #fff;
        text-decoration: none;
        transition: 0.3s;
    }

    .back-btn:hover {
        background: rgba(233,185,73,0.1);
        color: #e9b949;
    }

    .search-box {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }

    .search-form {
        display: flex;
        gap: 15px;
    }

    .search-input {
        flex: 1;
        padding: 15px 20px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 10px;
        color: #fff;
        font-size: 1rem;
    }

    .search-input:focus {
        outline: none;
        border-color: #e9b949;
        box-shadow: 0 0 0 3px rgba(233,185,73,0.15);
    }

    .search-btn {
        padding: 15px 30px;
        background: #e9b949;
        border: none;
        border-radius: 10px;
        color: #1a1a2e;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
    }

    .search-btn:hover {
        background: #d4a847;
        transform: translateY(-2px);
    }

    .order-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .order-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #e9b949;
    }

    .order-status {
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .status-pending { background: rgba(234, 179, 8, 0.2); color: #eab308; }
    .status-confirmed { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
    .status-processing { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
    .status-shipped { background: rgba(14, 165, 233, 0.2); color: #0ea5e9; }
    .status-delivered { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    .status-cancelled { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

    .tracking-timeline {
        position: relative;
        padding-left: 40px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 30px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -30px;
        top: 8px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        border: 3px solid #0a0a0a;
    }

    .timeline-item.active::before {
        background: #e9b949;
        box-shadow: 0 0 15px rgba(233,185,73,0.5);
    }

    .timeline-item::after {
        content: '';
        position: absolute;
        left: -23px;
        top: 24px;
        width: 2px;
        height: calc(100% - 8px);
        background: rgba(255,255,255,0.1);
    }

    .timeline-item:last-child::after {
        display: none;
    }

    .timeline-status {
        font-weight: 700;
        color: #e9b949;
        margin-bottom: 5px;
        text-transform: capitalize;
    }

    .timeline-date {
        font-size: 0.9rem;
        color: rgba(255,255,255,0.6);
        margin-bottom: 5px;
    }

    .timeline-note {
        color: rgba(255,255,255,0.8);
        font-size: 0.95rem;
    }

    .proof-box {
        background: rgba(255,255,255,0.05);
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        margin-top: 20px;
    }
    
    .proof-box img {
        max-width: 100%;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .proof-box small {
        color: rgba(255,255,255,0.6);
    }

    .order-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .detail-box {
        background: rgba(255,255,255,0.05);
        padding: 20px;
        border-radius: 12px;
    }

    .detail-label {
        color: rgba(255,255,255,0.6);
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .detail-value {
        color: #fff;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .no-order {
        text-align: center;
        padding: 60px 20px;
        color: rgba(255,255,255,0.5);
    }

    .no-order i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 15px;
        }

        .search-form {
            flex-direction: column;
        }

        .order-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-shipping-fast"></i> Track Your Order</h1>
            <a href="../../index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>

        <div class="search-box">
            <form method="GET" class="search-form">
                <input 
                    type="text" 
                    name="order_number" 
                    class="search-input" 
                    placeholder="Enter your order number (e.g., ORD202410300001)"
                    value="<?= htmlspecialchars($_GET['order_number'] ?? '') ?>"
                    required
                >
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Track Order
                </button>
            </form>
        </div>

        <?php if (isset($_GET['order_number'])): ?>
            <?php if ($order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">
                            Order #<?= htmlspecialchars($order['order_number']) ?>
                        </div>
                        <div class="order-status status-<?= $order['order_status'] ?>">
                            <?= ucfirst($order['order_status']) ?>
                        </div>
                    </div>

                    <div class="order-details-grid">
                        <div class="detail-box">
                            <div class="detail-label">Product</div>
                            <div class="detail-value"><?= htmlspecialchars($order['product_name']) ?></div>
                        </div>
                        <div class="detail-box">
                            <div class="detail-label">Quantity</div>
                            <div class="detail-value"><?= $order['quantity'] ?></div>
                        </div>
                        <div class="detail-box">
                            <div class="detail-label">Total Amount</div>
                            <div class="detail-value">₱<?= number_format($order['total_amount'], 2) ?></div>
                        </div>
                        <div class="detail-box">
                            <div class="detail-label">Order Date</div>
                            <div class="detail-value"><?= date('M j, Y', strtotime($order['created_at'])) ?></div>
                        </div>
                    </div>

                    <h3 style="color: #e9b949; margin: 30px 0 20px 0;">
                        <i class="fas fa-history"></i> Order Timeline
                    </h3>

                    <div class="tracking-timeline">
                        <?php 
                        $status_order = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
                        $current_status_index = array_search($order['order_status'], $status_order);
                        
                        foreach ($order_history as $history): 
                            $is_active = array_search($history['status'], $status_order) <= $current_status_index;
                        ?>
                            <div class="timeline-item <?= $is_active ? 'active' : '' ?>">
                                <div class="timeline-status"><?= ucfirst($history['status']) ?></div>
                                <div class="timeline-date">
                                    <?= date('F j, Y - g:i A', strtotime($history['created_at'])) ?>
                                </div>
                                <?php if (!empty($history['notes'])): ?>
                                    <div class="timeline-note"><?= htmlspecialchars($history['notes']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($order['order_status'] === 'delivered' && !empty($order['delivery_proof'])): ?>
                        <div class="proof-box">
                            <h3 style="color: #e9b949; margin-bottom: 15px;">
                                <i class="fas fa-image"></i> Proof of Delivery
                            </h3>
                            <img src="/STARROOFING/uploads/delivery_proofs/<?= htmlspecialchars($order['delivery_proof']) ?>" alt="Proof of Delivery">
                            <small>Delivery completed successfully.</small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="order-card">
                    <div class="no-order">
                        <i class="fas fa-search"></i>
                        <h2>Order Not Found</h2>
                        <p>No order found with that order number. Please check and try again.</p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>