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

    h1 i {
        color: #e9b949;
    }

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

    .item-list {
        display: grid;
        gap: 20px;
        margin-bottom: 40px;
    }

    .item-card {
        display: grid;
        grid-template-columns: auto 120px 1fr auto;
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

    .item-checkbox {
        width: 24px;
        height: 24px;
        cursor: pointer;
        accent-color: #e9b949;
    }

    .item-image {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .item-card img {
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
        box-shadow: 0 10px 30px rgba(233, 185, 73, 0.3);
    }

    .btn-checkout:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

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

    .selection-info {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px 20px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .select-all-container {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .select-all-container input {
        width: 24px;
        height: 24px;
        cursor: pointer;
        accent-color: #e9b949;
    }

    .select-all-container label {
        font-weight: 600;
        color: #e9b949;
        cursor: pointer;
        user-select: none;
    }

    .selected-count {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
    }

    @media (max-width: 968px) {
        .item-card {
            grid-template-columns: auto 100px 1fr;
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
    }

    @media (max-width: 768px) {
        body {
            padding: 40px 15px;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 30px;
        }

        .item-card {
            grid-template-columns: auto 80px 1fr;
            padding: 20px;
        }

        .item-image {
            width: 80px;
            height: 80px;
        }

        .cart-summary {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .item-card {
            grid-template-columns: 1fr;
            text-align: center;
            padding: 20px;
        }

        .item-checkbox {
            position: absolute;
            top: 20px;
            left: 20px;
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
                <div class="selection-info">
                    <div class="select-all-container" onclick="toggleSelectAll()">
                        <input type="checkbox" id="selectAll">
                        <label for="selectAll">Select All</label>
                    </div>
                    <span class="selected-count">
                        <span id="selectedCount">0</span> item(s) selected
                    </span>
                </div>

                <div class="item-list">
                    <?php 
                    while ($item = $cart_items->fetch_assoc()): 
                        $itemTotal = $item['price'] * $item['quantity'];
                    ?>
                        <div class="item-card">
                            <input type="checkbox" 
                                   class="item-checkbox select-item" 
                                   value="<?= $item['cart_id'] ?>"
                                   data-price="<?= $itemTotal ?>"
                                   onchange="updateTotal()">
                            
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
                    <span class="total-label">Selected Items Total:</span>
                    <span class="total-amount" id="totalAmount">₱0.00</span>
                </div>

                <div class="checkout-actions">
                    <button id="checkoutBtn" class="btn btn-checkout">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </button>
                </div>

            <?php else: ?>
                <div class="empty-text">
                    <i class="fas fa-shopping-cart"></i>
                    Your cart is empty.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Update total based on selected items
    function updateTotal() {
        const checkboxes = document.querySelectorAll('.select-item:checked');
        const selectAllCheckbox = document.getElementById('selectAll');
        const totalCheckboxes = document.querySelectorAll('.select-item');
        
        let total = 0;
        checkboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price);
        });
        
        document.getElementById('totalAmount').textContent = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('selectedCount').textContent = checkboxes.length;
        
        // Update select all checkbox state
        selectAllCheckbox.checked = checkboxes.length === totalCheckboxes.length && totalCheckboxes.length > 0;
    }

    // Toggle select all
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.select-item');
        
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
        });
        
        updateTotal();
    }

    // Proceed to checkout
    document.getElementById('checkoutBtn').addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.select-item:checked')).map(cb => cb.value);

        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No items selected',
                text: 'Please select at least one item to proceed to checkout.',
                confirmButtonColor: '#e9b949'
            });
            return;
        }

        // Redirect to checkout with selected cart IDs
        window.location.href = `checkout.php?cart_ids=${selected.join(',')}`;
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateTotal();
    });
    </script>

</body>
</html>