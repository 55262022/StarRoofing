<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

$account_id = $_SESSION['account_id'];

// Determine if it's cart checkout or single product checkout
$is_cart_checkout = isset($_GET['cart_ids']) && !empty($_GET['cart_ids']);
$cart_items = [];
$total_subtotal = 0;
$delivery_fee = 150;

if ($is_cart_checkout) {
    // Multiple items from cart
    $cart_ids = array_map('intval', explode(',', $_GET['cart_ids']));
    $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
    $types = str_repeat('i', count($cart_ids));
    
    $query = "
        SELECT c.cart_id, c.quantity, c.size, c.color, 
               p.product_id, p.name, p.price, p.image_path, cat.category_name
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        LEFT JOIN categories cat ON p.category_id = cat.category_id
        WHERE c.cart_id IN ($placeholders) AND c.account_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $bind_params = array_merge($cart_ids, [$account_id]);
    $stmt->bind_param($types . 'i', ...$bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $item_total = $row['price'] * $row['quantity'];
        $row['item_total'] = $item_total;
        $total_subtotal += $item_total;
        $cart_items[] = $row;
    }
    $stmt->close();
} else {
    // Single product checkout
    if (isset($_GET['product_id'])) {
        $product_id = (int)$_GET['product_id'];
        $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
        
        $query = "
            SELECT p.*, c.category_name
            FROM products AS p
            LEFT JOIN categories AS c ON p.category_id = c.category_id
            WHERE p.product_id = ? AND p.is_archived = 0
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($product) {
            $product['quantity'] = $quantity;
            $product['item_total'] = $product['price'] * $quantity;
            $total_subtotal = $product['item_total'];
            $cart_items[] = $product;
        }
    }
}

$total_amount = $total_subtotal + $delivery_fee;

// Get user info
$user_query = "
    SELECT up.*, a.email 
    FROM user_profiles up 
    JOIN accounts a ON up.account_id = a.id 
    WHERE up.account_id = ?
";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $account_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Get user addresses
$address_query = "SELECT * FROM user_addresses WHERE account_id = ? ORDER BY is_default DESC, created_at DESC";
$address_stmt = $conn->prepare($address_query);
$address_stmt->bind_param("i", $account_id);
$address_stmt->execute();
$addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$address_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - Star Roofing & Construction</title>
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

    .checkout-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }
    .checkout-hero h1 {
        font-size: 2rem;
        font-weight: 800;
        color: #e9b949;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .back-button {
        color: #fff;
        text-decoration: none;
        padding: 10px 18px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        transition: 0.3s;
    }
    .back-button:hover {
        background: rgba(233,185,73,0.1);
        color: #e9b949;
        transform: translateX(-5px);
    }

    .checkout-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 40px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 24px;
        padding: 40px;
        backdrop-filter: blur(10px);
    }
    @media (max-width: 900px) {
        .checkout-content { grid-template-columns: 1fr; }
    }

    .checkout-form h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #e9b949;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-section {
        background: rgba(255,255,255,0.03);
        padding: 25px;
        border-radius: 15px;
        border: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 25px;
    }
    .form-row {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .form-group {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    label {
        margin-bottom: 8px;
        font-weight: 600;
        color: rgba(255,255,255,0.8);
    }
    input, select, textarea {
        width: 100%;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.05);
        color: #fff;
        font-size: 1rem;
        transition: 0.3s;
        resize: none;
    }
    input:focus, select:focus, textarea:focus {
        border-color: #e9b949;
        background: rgba(255,255,255,0.08);
        box-shadow: 0 0 0 3px rgba(233,185,73,0.15);
        outline: none;
    }

    .address-selection {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }
    .address-card {
        background: rgba(255,255,255,0.05);
        border: 2px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 15px;
        cursor: pointer;
        transition: 0.3s;
        position: relative;
    }
    .address-card:hover {
        background: rgba(255,255,255,0.08);
        border-color: rgba(233,185,73,0.3);
        transform: translateY(-2px);
    }
    .address-card.selected {
        background: rgba(233,185,73,0.15);
        border-color: #e9b949;
        box-shadow: 0 0 20px rgba(233,185,73,0.3);
    }
    .address-card input[type="radio"] {
        display: none;
    }
    .address-label {
        font-size: 1rem;
        font-weight: 700;
        color: #e9b949;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .default-badge {
        background: #e9b949;
        color: #1a1a2e;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 20px;
        font-weight: 600;
    }
    .address-details {
        color: rgba(255,255,255,0.7);
        font-size: 0.85rem;
        line-height: 1.5;
    }
    .add-address-btn {
        background: rgba(255,255,255,0.05);
        border: 2px dashed rgba(255,255,255,0.2);
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: rgba(255,255,255,0.6);
        font-weight: 600;
    }
    .add-address-btn:hover {
        background: rgba(233,185,73,0.1);
        border-color: #e9b949;
        color: #e9b949;
    }

    .payment-methods {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    .payment-option {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 15px;
        cursor: pointer;
        transition: 0.3s;
    }
    .payment-option:hover {
        border-color: #e9b949;
        transform: translateY(-3px);
    }
    .payment-option.selected {
        background: rgba(233,185,73,0.15);
        border-color: #e9b949;
        box-shadow: 0 0 15px rgba(233,185,73,0.3);
    }
    .payment-option input { display: none; }
    .payment-option label {
        font-weight: 600;
        cursor: pointer;
        color: #fff;
    }

    .btn-primary {
        background: #e9b949;
        color: #1a1a2e;
        border: none;
        padding: 15px 25px;
        font-weight: 700;
        border-radius: 12px;
        cursor: pointer;
        transition: 0.3s;
        width: 100%;
        font-size: 1rem;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-primary:hover {
        background: transparent;
        border: 2px solid #e9b949;
        color: #e9b949;
        box-shadow: 0 0 25px rgba(233,185,73,0.4);
        transform: translateY(-3px);
    }

    .order-summary {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 25px;
        height: fit-content;
        position: sticky;
        top: 20px;
    }
    .order-summary h3 {
        color: #e9b949;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.3rem;
    }
    
    .product-list {
        max-height: 400px;
        overflow-y: auto;
        margin-bottom: 20px;
    }
    
    .product-summary {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        padding: 12px;
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .product-summary-image img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .product-summary-info {
        flex: 1;
    }
    .product-summary-info h4 {
        font-size: 0.95rem;
        margin-bottom: 5px;
    }
    .product-summary-info .category {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.5);
    }
    .product-summary-info .quantity {
        font-size: 0.85rem;
        color: #e9b949;
        margin-top: 3px;
    }
    .product-summary-price {
        font-weight: 700;
        color: #e9b949;
        font-size: 1rem;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding: 8px 0;
        color: rgba(255,255,255,0.8);
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .summary-row.total {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 2px solid rgba(233,185,73,0.3);
        border-bottom: none;
        font-weight: 700;
        color: #e9b949;
        font-size: 1.3rem;
    }
</style>
</head>
<body>
    <div class="checkout-hero">
        <a href="javascript:history.back()" class="back-button">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h1><i class="fa fa-shopping-cart"></i> Checkout</h1>
    </div>

    <div class="checkout-content">
        <div class="checkout-form">
            <form id="checkoutForm" method="POST" action="../process/process-order.php">
                <?php if ($is_cart_checkout): ?>
                    <input type="hidden" name="is_cart_checkout" value="1">
                    <?php foreach ($cart_items as $item): ?>
                        <input type="hidden" name="cart_ids[]" value="<?= $item['cart_id'] ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <input type="hidden" name="product_id" value="<?= $cart_items[0]['product_id'] ?? '' ?>">
                    <input type="hidden" name="quantity" value="<?= $cart_items[0]['quantity'] ?? 1 ?>">
                <?php endif; ?>

                <div class="form-section">
                    <h3><i class="fa fa-user"></i> Customer Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fa fa-map-marker-alt"></i> Delivery Address</h3>
                    
                    <?php if (!empty($addresses)): ?>
                        <div class="address-selection">
                            <?php foreach ($addresses as $address): ?>
                                <div class="address-card" onclick="selectAddress(<?= $address['id'] ?>)">
                                    <input type="radio" name="address_id" id="addr_<?= $address['id'] ?>" value="<?= $address['id'] ?>" <?= $address['is_default'] ? 'checked' : '' ?> required>
                                    <div class="address-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($address['address_label']) ?>
                                        <?php if ($address['is_default']): ?>
                                            <span class="default-badge">DEFAULT</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="address-details">
                                        <?php 
                                        $parts = array_filter([
                                            $address['street'],
                                            $address['barangay_name'],
                                            $address['city_name'],
                                            $address['province_name']
                                        ]);
                                        echo htmlspecialchars(implode(', ', $parts));
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="add-address-btn" onclick="openAddAddressModal()">
                                <i class="fas fa-plus"></i>
                                Add New Address
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="address-selection">
                            <div class="add-address-btn" onclick="openAddAddressModal()">
                                <i class="fas fa-plus"></i>
                                Add Your First Address
                            </div>
                        </div>
                        <input type="hidden" name="address_id" id="hidden_address_id" required>
                    <?php endif; ?>
                    
                    <div class="form-group" style="margin-top: 15px;">
                        <label>Delivery Notes (Optional)</label>
                        <textarea name="delivery_notes" placeholder="Any special instructions for delivery" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fa fa-credit-card"></i> Payment Method</h3>
                    <div class="payment-methods">
                        <div class="payment-option" onclick="selectPayment('cod')">
                            <input type="radio" name="payment_method" id="cod" value="cod" required checked>
                            <label for="cod"><i class="fa fa-money-bill-wave"></i> Cash on Delivery</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fa fa-check"></i> Place Order
                    </button>
                </div>
            </form>
        </div>

        <div class="order-summary">
            <h3><i class="fa fa-receipt"></i> Order Summary</h3>
            
            <div class="product-list">
                <?php foreach ($cart_items as $item): ?>
                    <div class="product-summary">
                        <div class="product-summary-image">
                            <img src="/STARROOFING/<?= htmlspecialchars($item['image_path'] ?? 'images/no-image.png') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        </div>
                        <div class="product-summary-info">
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <div class="category"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></div>
                            <div class="quantity">Qty: <?= $item['quantity'] ?></div>
                        </div>
                        <div class="product-summary-price">
                            ₱<?= number_format($item['item_total'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-row">
                <span>Subtotal (<?= count($cart_items) ?> item<?= count($cart_items) > 1 ? 's' : '' ?>):</span>
                <strong>₱<?= number_format($total_subtotal, 2) ?></strong>
            </div>
            <div class="summary-row">
                <span>Delivery Fee:</span>
                <strong>₱<?= number_format($delivery_fee, 2) ?></strong>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <strong>₱<?= number_format($total_amount, 2) ?></strong>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    function selectAddress(addressId) {
        document.querySelectorAll('.address-card').forEach(card => {
            card.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        document.getElementById('addr_' + addressId).checked = true;
        
        const hiddenField = document.getElementById('hidden_address_id');
        if (hiddenField) {
            hiddenField.value = addressId;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const defaultAddress = document.querySelector('.address-card input[checked]');
        if (defaultAddress) {
            defaultAddress.closest('.address-card').classList.add('selected');
        }
    });

    function selectPayment(method) {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        event.currentTarget.classList.add('selected');
        document.getElementById(method).checked = true;
    }

    async function openAddAddressModal() {
        // Same implementation as before
        alert('Add address modal - implement same as original');
    }

    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const addressId = document.querySelector('input[name="address_id"]:checked');
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        
        if (!addressId) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Address Required',
                text: 'Please select a delivery address!',
                confirmButtonColor: '#e9b949'
            });
            return;
        }
        
        if (!paymentMethod) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Payment Method Required',
                text: 'Please select a payment method!',
                confirmButtonColor: '#e9b949'
            });
            return;
        }
    });
    </script>
</body>
</html>