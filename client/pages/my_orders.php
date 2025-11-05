<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

$account_id = $_SESSION['account_id'];

// 📦 Get all orders for this user
$order_query = "
    SELECT o.order_number, o.product_name, o.quantity, o.total_amount, o.order_status, o.created_at, p.image_path
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.product_id
    WHERE o.account_id = ?
    ORDER BY o.created_at DESC
";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("i", $account_id);
$order_stmt->execute();
$order_items = $order_stmt->get_result();
$order_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders - Star Roofing & Construction</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Montserrat', sans-serif;
        background: rgba(21, 21, 41, 0.95);
        color: #fff;
        min-height: 100vh;
        padding: 60px 20px;
        line-height: 1.6;
    }
    .orders-container {
        max-width: 1200px;
        margin: 0 auto;
        animation: fadeInUp 0.6s ease;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #e9b949;
        text-decoration: none;
        margin-bottom: 40px;
        transition: all 0.3s ease;
        font-weight: 600;
        padding: 12px 24px;
        background: rgba(233, 185, 73, 0.05);
        border-radius: 50px;
        border: 2px solid rgba(233, 185, 73, 0.2);
        font-size: 0.9rem;
        letter-spacing: 0.05em;
    }
    .back-link:hover {
        gap: 15px;
        background: rgba(233, 185, 73, 0.1);
        border-color: rgba(233, 185, 73, 0.4);
        transform: translateX(-5px);
    }
    h1 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        color: white;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    h1 i { color: #e9b949; }

    /* ====== STATUS TABS ====== */
    .status-tabs {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin: 20px 0 40px;
        flex-wrap: wrap;
    }
    .tab-btn {
        flex: 1;
        min-width: 130px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 15px 10px;
        border-radius: 15px;
        text-align: center;
        color: #ccc;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .tab-btn i { font-size: 1.5rem; color: #e9b949; }
    .tab-btn.active {
        background: rgba(233, 185, 73, 0.15);
        border-color: rgba(233, 185, 73, 0.6);
        color: #fff;
        transform: translateY(-3px);
    }

    .item-list { display: grid; gap: 20px; margin-bottom: 40px; }
    .order-card-clickable {
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: grid;
        grid-template-columns: 120px 1fr;
        align-items: center;
        gap: 25px;
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 25px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .order-card-clickable:hover {
        background: rgba(233, 185, 73, 0.05);
        border-color: rgba(233, 185, 73, 0.3);
        transform: translateY(-5px);
    }

    .item-image {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .order-card-clickable img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-info { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .item-info h3 { font-size: 1.2rem; font-weight: 600; color: #fff; margin-bottom: 5px; }
    .item-info > p {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.9rem;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .item-info > p i { color: #e9b949; font-size: 0.85rem; }

    .item-meta { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 12px; }
    .meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(233, 185, 73, 0.1);
        border: 1px solid rgba(233, 185, 73, 0.3);
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #e9b949;
        letter-spacing: 0.05em;
    }

    .price { font-weight: 700; color: #e9b949; font-size: 1.4rem; margin-top: 8px; }
    .empty-text {
        text-align: center;
        color: rgba(255, 255, 255, 0.5);
        margin: 60px 0;
        font-size: 1.1rem;
        padding: 60px 40px;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 20px;
        border: 1px dashed rgba(255, 255, 255, 0.1);
    }
    .review-btn {
        display: inline-block;
        margin-top: 12px;
        padding: 8px 14px;
        background: #e9b949;
        color: #000;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .review-btn:hover {
        background: #fff3c2;
    }
</style>
</head>
<body>

<div class="orders-container">
    <a href="../../index.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Home</span>
    </a>

    <h1><i class="fa-solid fa-clipboard-list"></i> My Orders</h1>

    <!-- 🔹 STATUS FILTER TABS -->
    <div class="status-tabs">
        <div class="tab-btn active" data-status="to-pay">
            <i class="fa-solid fa-wallet"></i>
            <span>To Pay</span>
        </div>
        <div class="tab-btn" data-status="to-ship">
            <i class="fa-solid fa-truck-fast"></i>
            <span>To Ship</span>
        </div>
        <div class="tab-btn" data-status="to-receive">
            <i class="fa-solid fa-box-open"></i>
            <span>To Receive</span>
        </div>
        <div class="tab-btn" data-status="delivered">
            <i class="fa-solid fa-check-circle"></i>
            <span>Delivered</span>
        </div>
    </div>

    <?php
    $orders = [
        'to-pay' => [],
        'to-ship' => [],
        'to-receive' => [],
        'delivered' => []
    ];

    while ($order = $order_items->fetch_assoc()) {
        if ($order['order_status'] === 'pending') {
            $orders['to-pay'][] = $order;
        } elseif ($order['order_status'] === 'confirmed') {
            $orders['to-ship'][] = $order;
        } elseif ($order['order_status'] === 'to ship') {
            $orders['to-receive'][] = $order;
        } elseif ($order['order_status'] === 'delivered') {
            $orders['delivered'][] = $order;
        }
    }
    ?>

    <!-- 🔹 ORDER SECTIONS -->
    <?php foreach ($orders as $status => $items): ?>
        <div class="item-list order-section" id="<?= $status ?>" style="<?= $status !== 'to-pay' ? 'display:none;' : '' ?>">
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $order): ?>
                    <a href="track-order.php?order_number=<?= urlencode($order['order_number']) ?>" class="order-card-clickable">
                        <div class="item-image">
                            <img src="/STARROOFING/<?= htmlspecialchars($order['image_path']) ?>" alt="<?= htmlspecialchars($order['product_name']) ?>">
                        </div>
                        <div class="item-info">
                            <h3><?= htmlspecialchars($order['product_name']) ?></h3>
                            <p><i class="fas fa-receipt"></i> Order #: <?= htmlspecialchars($order['order_number']) ?></p>
                            <div class="item-meta">
                                <span class="meta-badge"><i class="fas fa-boxes"></i> Qty: <?= (int)$order['quantity'] ?></span>
                                <span class="meta-badge"><i class="fas fa-info-circle"></i> <?= ucfirst($order['order_status']) ?></span>
                            </div>
                            <p><i class="far fa-calendar-alt"></i> <?= date('F j, Y - g:i A', strtotime($order['created_at'])) ?></p>
                            <p class="price">₱<?= number_format($order['total_amount'], 2) ?></p>

                            <?php if ($status === 'delivered'): ?>
                                <a href="review.php?order_number=<?= urlencode($order['order_number']) ?>" class="review-btn">
                                    <i class="fa-solid fa-star"></i> Leave a Review
                                </a>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-text">No orders in this category.</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>

<script>
const tabs = document.querySelectorAll('.tab-btn');
const sections = document.querySelectorAll('.order-section');

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const status = tab.dataset.status;
        sections.forEach(section => {
            section.style.display = section.id === status ? 'grid' : 'none';
        });
    });
});
</script>

</body>
</html>
