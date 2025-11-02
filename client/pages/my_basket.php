<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

$account_id = $_SESSION['account_id'];

$cart_query = "
    SELECT c.cart_id, c.quantity, c.size, c.color, p.product_id, p.name AS product_name, p.price, p.image_path, cat.category_name
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    LEFT JOIN categories cat ON p.category_id = cat.category_id
    WHERE c.account_id = ?
";
$cart_stmt = $conn->prepare($cart_query);
if (!$cart_stmt) {
    die("SQL prepare failed for cart_query: " . $conn->error);
}
$cart_stmt->bind_param("i", $account_id);
$cart_stmt->execute();
$cart_items = $cart_stmt->get_result();
$cart_stmt->close();

// 📦 Get previous orders
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
<title>My Basket - Star Roofing & Construction</title>
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
        background: rgba(21, 21, 41, 0.95);
        color: #fff;
        min-height: 100vh;
        padding: 60px 20px;
        line-height: 1.6;
    }

    .basket-container {
        max-width: 1200px;
        margin: 0 auto;
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

    /* Back Link */
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

    /* Page Title */
    h1 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        color: white;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    h1 i {
        color: #e9b949;
    }

    /* Section Styling */
    .section {
        margin-bottom: 60px;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: white;
        margin-bottom: 30px;
        padding-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .section-title i {
        color: #e9b949;
        font-size: 1.3rem;
    }

    .section-label {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.2em;
        color: #e9b949;
        margin-bottom: 1rem;
        text-transform: uppercase;
    }

    /* Item List */
    .item-list {
        display: grid;
        gap: 20px;
        margin-bottom: 40px;
    }

    /* Item Cards */
    .item-card {
        display: grid;
        grid-template-columns: 120px 1fr auto;
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

    .item-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #e9b949, transparent);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .item-card:hover {
        background: rgba(233, 185, 73, 0.05);
        border-color: rgba(233, 185, 73, 0.3);
        transform: translateY(-5px);
    }

    .item-card:hover::before {
        transform: scaleX(1);
    }

    /* Clickable order card */
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

    .order-card-clickable::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #e9b949, transparent);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .order-card-clickable::after {
        content: '\f061';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        color: #e9b949;
        font-size: 1.5rem;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .order-card-clickable:hover {
        background: rgba(233, 185, 73, 0.05);
        border-color: rgba(233, 185, 73, 0.3);
        transform: translateY(-5px);
        padding-right: 70px;
    }

    .order-card-clickable:hover::before {
        transform: scaleX(1);
    }

    .order-card-clickable:hover::after {
        opacity: 1;
        right: 30px;
    }

    .item-image {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .item-card img, .order-card-clickable img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .item-info h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #fff;
        margin-bottom: 5px;
    }

    .item-info > p {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.9rem;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .item-info > p i {
        color: #e9b949;
        font-size: 0.85rem;
    }

    .item-meta {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

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

    .meta-badge i {
        font-size: 0.9rem;
    }

    .price {
        font-weight: 700;
        color: #e9b949;
        font-size: 1.4rem;
        margin-top: 8px;
    }

    /* Item Actions */
    .item-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 140px;
    }

    .btn {
        background: transparent;
        color: #e9b949;
        padding: 12px 24px;
        border-radius: 50px;
        border: 2px solid #e9b949;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        white-space: nowrap;
        font-family: 'Montserrat', sans-serif;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .btn:hover {
        background: #e9b949;
        color: #1a1a2e;
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(233, 185, 73, 0.3);
    }

    .btn-remove {
        border-color: #e74c3c;
        color: #e74c3c;
    }

    .btn-remove:hover {
        background: #e74c3c;
        color: #fff;
        box-shadow: 0 10px 30px rgba(231, 76, 60, 0.3);
    }

    .btn-checkout {
        background: #e9b949;
        border-color: #e9b949;
        color: #1a1a2e;
        font-size: 1rem;
        padding: 16px 40px;
        font-weight: 700;
    }

    .btn-checkout:hover {
        background: transparent;
        color: #e9b949;
        box-shadow: 0 10px 30px rgba(46, 204, 113, 0.3);
    }

    /* Empty State */
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

    .empty-text i {
        font-size: 4rem;
        color: rgba(255, 255, 255, 0.2);
        display: block;
        margin-bottom: 20px;
    }

    /* Cart Summary */
    .cart-summary {
        background: rgba(233, 185, 73, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(233, 185, 73, 0.2);
        border-radius: 20px;
        padding: 30px;
        margin-top: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-summary .total-label {
        font-size: 1.2rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
        letter-spacing: 0.05em;
    }

    .cart-summary .total-amount {
        font-size: 2.2rem;
        font-weight: 800;
        color: #e9b949;
    }

    .checkout-actions {
        text-align: right;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Click to view hint */
    .view-hint {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: rgba(233, 185, 73, 0.7);
        margin-top: 10px;
        font-style: italic;
    }

    /* Responsive Design */
    @media (max-width: 968px) {
        .item-card {
            grid-template-columns: 100px 1fr;
            gap: 20px;
        }

        .item-actions {
            grid-column: 1 / -1;
            flex-direction: row;
            justify-content: stretch;
        }

        .item-actions .btn {
            flex: 1;
        }

        .order-card-clickable {
            grid-template-columns: 100px 1fr;
        }
    }

    @media (max-width: 768px) {
        body {
            padding: 40px 15px;
        }

        .basket-container {
            padding: 0;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 30px;
        }

        .item-card, .order-card-clickable {
            grid-template-columns: 80px 1fr;
            padding: 20px;
        }

        .order-card-clickable:hover {
            padding-right: 20px;
        }

        .order-card-clickable::after {
            font-size: 1.2rem;
            right: 20px;
        }

        .order-card-clickable:hover::after {
            right: 20px;
        }

        .item-image {
            width: 80px;
            height: 80px;
        }

        .item-info h3 {
            font-size: 1rem;
        }

        .price {
            font-size: 1.2rem;
        }

        .cart-summary {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .cart-summary .total-amount {
            font-size: 2rem;
        }
    }

    @media (max-width: 480px) {
        .item-card, .order-card-clickable {
            grid-template-columns: 1fr;
            text-align: center;
            padding: 20px;
        }

        .item-image {
            width: 100%;
            height: 200px;
            margin: 0 auto;
        }

        .item-meta {
            justify-content: center;
        }

        .item-info {
            align-items: center;
        }

        .item-info > p {
            justify-content: center;
        }

        .checkout-actions {
            text-align: center;
        }

        .btn-checkout {
            width: 100%;
        }

        .empty-text {
            padding: 40px 20px;
        }
    }
</style>
</head>
<body>

    <div class="basket-container">
        <a href="../../index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Home</span>
        </a>
        
        <span class="section-label">Shopping Cart</span>
        <h1><i class="fas fa-shopping-basket"></i> My Basket</h1>

        <div class="section">
            <div class="section-title">
                <i class="fas fa-cart-plus"></i> Items in Cart
            </div>
            <?php if ($cart_items->num_rows > 0): ?>
                <div class="item-list">
                    <?php 
                    $total = 0;
                    while ($item = $cart_items->fetch_assoc()): 
                        $itemTotal = $item['price'] * $item['quantity'];
                        $total += $itemTotal;
                    ?>
                        <div class="item-card">
                            <div class="item-image">
                                <img src="/STARROOFING/<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                            </div>
                            <div class="item-info">
                                <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                                <p><i class="fas fa-tag"></i> <?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></p>
                                
                                <div class="item-meta">
                                    <?php if (!empty($item['size'])): ?>
                                        <span class="meta-badge">
                                            <i class="fas fa-ruler"></i> <?= htmlspecialchars($item['size']) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($item['color'])): ?>
                                        <span class="meta-badge">
                                            <i class="fas fa-palette"></i> <?= htmlspecialchars($item['color']) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="meta-badge">
                                        <i class="fas fa-boxes"></i> Qty: <?= (int)$item['quantity'] ?>
                                    </span>
                                </div>
                                
                                <p class="price">₱<?= number_format($itemTotal, 2) ?></p>
                            </div>
                            
                            <div class="item-actions">
                                <form method="POST" action="../actions/remove_from_cart.php" onsubmit="return confirm('Remove this item from your cart?');">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <button type="submit" class="btn btn-remove">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="cart-summary">
                    <span class="total-label">Cart Total:</span>
                    <span class="total-amount">₱<?= number_format($total, 2) ?></span>
                </div>

                <div class="checkout-actions">
                    <a href="../checkout.php" class="btn btn-checkout">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </a>
                </div>

            <?php else: ?>
                <div class="empty-text">
                    <i class="fas fa-shopping-cart"></i>
                    Your cart is empty.
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <div class="section-title">
                <i class="fas fa-box-open"></i> My Orders
            </div>
            <?php if ($order_items->num_rows > 0): ?>
                <div class="item-list">
                    <?php while ($order = $order_items->fetch_assoc()): ?>
                        <a href="track-order.php?order_number=<?= urlencode($order['order_number']) ?>" class="order-card-clickable">
                            <div class="item-image">
                                <img src="/STARROOFING/<?= htmlspecialchars($order['image_path']) ?>" alt="<?= htmlspecialchars($order['product_name']) ?>">
                            </div>
                            <div class="item-info">
                                <h3><?= htmlspecialchars($order['product_name']) ?></h3>
                                <p><i class="fas fa-receipt"></i> Order #: <?= htmlspecialchars($order['order_number']) ?></p>
                                
                                <div class="item-meta">
                                    <span class="meta-badge">
                                        <i class="fas fa-boxes"></i> Qty: <?= (int)$order['quantity'] ?>
                                    </span>
                                    <span class="meta-badge" style="border-color: <?= $order['order_status'] === 'pending' ? '#f39c12' : ($order['order_status'] === 'delivered' ? '#2ecc71' : ($order['order_status'] === 'cancelled' ? '#e74c3c' : '#3b82f6')) ?>; color: <?= $order['order_status'] === 'pending' ? '#f39c12' : ($order['order_status'] === 'delivered' ? '#2ecc71' : ($order['order_status'] === 'cancelled' ? '#e74c3c' : '#3b82f6')) ?>;">
                                        <i class="fas fa-info-circle"></i> <?= ucfirst($order['order_status']) ?>
                                    </span>
                                </div>
                                
                                <p><i class="far fa-calendar-alt"></i> <?= date('F j, Y - g:i A', strtotime($order['created_at'])) ?></p>
                                <p class="price">₱<?= number_format($order['total_amount'], 2) ?></p>
                                
                                <span class="view-hint">
                                    <i class="fas fa-mouse-pointer"></i> Click to view order details
                                </span>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-text">
                    <i class="fas fa-box"></i>
                    No previous orders yet.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>