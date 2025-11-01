<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

// ✅ Get product details
$product = null;
$quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
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
    if (!$product) header('Location: materials.php');
}

// ✅ Get user info (JOIN with accounts table to get email)
$user_query = "
    SELECT up.*, a.email 
    FROM user_profiles up 
    JOIN accounts a ON up.account_id = a.id 
    WHERE up.account_id = ?
";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $_SESSION['account_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// ✅ Get user addresses
$address_query = "SELECT * FROM user_addresses WHERE account_id = ? ORDER BY is_default DESC, created_at DESC";
$address_stmt = $conn->prepare($address_query);
$address_stmt->bind_param("i", $_SESSION['account_id']);
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

    /* Header */
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

    /* Layout */
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

    /* Form */
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

    /* Address Cards */
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
    .empty-state {
        text-align: center;
        padding: 30px;
        color: rgba(255,255,255,0.4);
        grid-column: 1 / -1;
    }

    /* Payment */
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

    /* Payment Details */
    .payment-details {
        display: none;
        margin-top: 20px;
        padding: 20px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(233,185,73,0.3);
        border-radius: 12px;
        animation: slideDown 0.3s ease;
    }
    .payment-details.active {
        display: block;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .payment-details h4 {
        color: #e9b949;
        margin-bottom: 15px;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .payment-info {
        display: grid;
        gap: 12px;
    }
    .payment-info-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: rgba(255,255,255,0.03);
        border-radius: 8px;
    }
    .payment-info-row i {
        color: #e9b949;
        font-size: 1.2rem;
    }
    .payment-info-label {
        font-weight: 600;
        color: rgba(255,255,255,0.7);
        min-width: 100px;
    }
    .payment-info-value {
        color: #fff;
        font-weight: 500;
    }
    .qr-code-container {
        text-align: center;
        padding: 20px;
        background: #fff;
        border-radius: 12px;
        margin: 15px 0;
    }
    .qr-code-container img {
        max-width: 250px;
        height: auto;
        border-radius: 8px;
    }
    .payment-note {
        padding: 12px;
        background: rgba(233,185,73,0.1);
        border-left: 3px solid #e9b949;
        border-radius: 8px;
        margin-top: 15px;
    }
    .payment-note i {
        color: #e9b949;
        margin-right: 8px;
    }
    .payment-note p {
        color: rgba(255,255,255,0.8);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    /* Button */
    .form-actions {
        text-align: right;
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
    }
    .btn-primary:hover {
        background: transparent;
        border: 2px solid #e9b949;
        color: #e9b949;
        box-shadow: 0 0 25px rgba(233,185,73,0.4);
        transform: translateY(-3px);
    }

    /* Sidebar */
    .order-summary {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 25px;
        height: fit-content;
    }
    .order-summary h3 {
        color: #e9b949;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .product-summary {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .product-summary-image img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .product-summary-info {
        margin-left: 15px;
    }
    .product-summary-info h4 {
        font-size: 1rem;
        margin-bottom: 5px;
    }
    .category {
        font-size: 0.9rem;
        color: rgba(255,255,255,0.6);
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        color: rgba(255,255,255,0.8);
    }
    .summary-row.total {
        margin-top: 15px;
        font-weight: 700;
        color: #e9b949;
        font-size: 1.1rem;
    }

    /* SweetAlert Custom Styles */
    .swal2-popup {
        background: #1a1a2e !important;
        color: #ffffff !important;
    }
    .swal2-title {
        color: #e9b949 !important;
    }
    .swal2-html-container {
        color: rgba(255, 255, 255, 0.9) !important;
    }
    .swal2-input, .swal2-select, .swal2-textarea {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
    }
    .swal2-select option {
        background: #1a1a2e !important;
        color: #ffffff !important;
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
        <!-- Unified Checkout Form -->
        <div class="checkout-form">
            <form id="checkoutForm" method="POST" action="../process/process-order.php">
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?? '' ?>">
                <input type="hidden" name="quantity" value="<?= $quantity ?>">

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
                            <div class="empty-state">
                                <i class="fas fa-map-marker-alt" style="font-size: 3rem; opacity: 0.3; margin-bottom: 10px;"></i>
                                <p>No saved addresses found.</p>
                            </div>
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
                            <input type="radio" name="payment_method" id="cod" value="cod" required>
                            <label for="cod"><i class="fa fa-money-bill-wave"></i> Cash on Delivery</label>
                        </div>
                        <!-- <div class="payment-option" onclick="selectPayment('gcash')">
                            <input type="radio" name="payment_method" id="gcash" value="gcash">
                            <label for="gcash"><i class="fa fa-mobile-alt"></i> GCash</label>
                        </div>
                        <div class="payment-option" onclick="selectPayment('bank')">
                            <input type="radio" name="payment_method" id="bank" value="bank">
                            <label for="bank"><i class="fa fa-university"></i> Bank Transfer</label>
                        </div> -->
                    </div>

                    <!-- GCash Payment Details -->
                    <div id="gcash-details" class="payment-details">
                        <h4><i class="fas fa-mobile-alt"></i> GCash Payment Details</h4>
                        <div class="qr-code-container">
                            <img src="../../assets/images/gcash-qr.png" alt="GCash QR Code" onerror="this.style.display='none'">
                        </div>
                        <div class="payment-info">
                            <div class="payment-info-row">
                                <i class="fas fa-user"></i>
                                <span class="payment-info-label">Account Name:</span>
                                <span class="payment-info-value">Star Roofing & Construction</span>
                            </div>
                            <div class="payment-info-row">
                                <i class="fas fa-phone"></i>
                                <span class="payment-info-label">GCash Number:</span>
                                <span class="payment-info-value">0917 123 4567</span>
                            </div>
                        </div>
                        <div class="payment-note">
                            <i class="fas fa-info-circle"></i>
                            <p><strong>Instructions:</strong> Scan the QR code or send payment to the GCash number above. After payment, please take a screenshot of the receipt and upload it when placing your order.</p>
                        </div>
                    </div>

                    <!-- Bank Transfer Payment Details -->
                    <div id="bank-details" class="payment-details">
                        <h4><i class="fas fa-university"></i> Bank Transfer Details</h4>
                        <div class="payment-info">
                            <div class="payment-info-row">
                                <i class="fas fa-building"></i>
                                <span class="payment-info-label">Bank Name:</span>
                                <span class="payment-info-value">BDO Unibank</span>
                            </div>
                            <div class="payment-info-row">
                                <i class="fas fa-user"></i>
                                <span class="payment-info-label">Account Name:</span>
                                <span class="payment-info-value">Star Roofing & Construction Inc.</span>
                            </div>
                            <div class="payment-info-row">
                                <i class="fas fa-credit-card"></i>
                                <span class="payment-info-label">Account Number:</span>
                                <span class="payment-info-value">0012 3456 7890</span>
                            </div>
                            <div class="payment-info-row">
                                <i class="fas fa-hashtag"></i>
                                <span class="payment-info-label">Account Type:</span>
                                <span class="payment-info-value">Current Account</span>
                            </div>
                        </div>
                        <div class="payment-note">
                            <i class="fas fa-info-circle"></i>
                            <p><strong>Instructions:</strong> Transfer the total amount to the bank account above. Please use your order number as reference and keep the deposit slip or transaction receipt for verification.</p>
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

        <!-- Order Summary Sidebar -->
        <div class="order-summary">
            <h3><i class="fa fa-receipt"></i> Order Summary</h3>
            <?php if ($product): ?>
                <div class="product-summary">
                    <div class="product-summary-image">
                        <img src="/STARROOFING/<?= htmlspecialchars($product['image_path'] ?? 'images/no-image.png') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <div class="product-summary-info">
                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <div class="category"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></div>
                    </div>
                </div>

                <?php
                    $subtotal = $product['price'] * $quantity;
                    $deliveryFee = 150;
                    $total = $subtotal + $deliveryFee;
                ?>

                <div class="summary-row"><span>Subtotal:</span><strong>₱<?= number_format($subtotal, 2) ?></strong></div>
                <div class="summary-row"><span>Quantity:</span><strong><?= $quantity ?></strong></div>
                <div class="summary-row"><span>Delivery Fee:</span><strong>₱<?= number_format($deliveryFee, 2) ?></strong></div>
                <div class="summary-row total"><span>Total:</span><strong>₱<?= number_format($total, 2) ?></strong></div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // Check for error messages from session
    <?php if (isset($_SESSION['checkout_error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Order Failed',
        text: '<?= addslashes($_SESSION['checkout_error']) ?>',
        confirmButtonColor: '#e9b949'
    });
    <?php unset($_SESSION['checkout_error']); ?>
    <?php endif; ?>
    
    // Select address card
    function selectAddress(addressId) {
        document.querySelectorAll('.address-card').forEach(card => {
            card.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        document.getElementById('addr_' + addressId).checked = true;
        
        // Update hidden field if exists
        const hiddenField = document.getElementById('hidden_address_id');
        if (hiddenField) {
            hiddenField.value = addressId;
        }
    }

    // Auto-select default address on load
    document.addEventListener('DOMContentLoaded', function() {
        const defaultAddress = document.querySelector('.address-card input[checked]');
        if (defaultAddress) {
            defaultAddress.closest('.address-card').classList.add('selected');
        }
    });

    // Select payment method
    function selectPayment(method) {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        event.currentTarget.classList.add('selected');
        document.getElementById(method).checked = true;
        
        // Hide all payment details
        document.querySelectorAll('.payment-details').forEach(detail => {
            detail.classList.remove('active');
        });
        
        // Show relevant payment details
        if (method === 'gcash') {
            document.getElementById('gcash-details').classList.add('active');
        } else if (method === 'bank') {
            document.getElementById('bank-details').classList.add('active');
        }
    }

    // Open add address modal
    async function openAddAddressModal() {
        const { value: formValues } = await Swal.fire({
            title: 'Add New Address',
            html: `
                <style>
                    .address-form-container select {
                        background: rgba(255, 255, 255, 0.1) !important;
                        color: #ffffff !important;
                        border: 1px solid rgba(255, 255, 255, 0.2) !important;
                        padding: 0.75rem !important;
                        border-radius: 8px !important;
                    }
                    .address-form-container select option {
                        background: #2a2a3e !important;
                        color: #ffffff !important;
                    }
                    .address-form-container input,
                    .address-form-container textarea {
                        background: rgba(255, 255, 255, 0.1) !important;
                        color: #ffffff !important;
                        border: 1px solid rgba(255, 255, 255, 0.2) !important;
                        padding: 0.75rem !important;
                        border-radius: 8px !important;
                    }
                    .address-form-container label {
                        display: block;
                        margin-bottom: 0.5rem;
                        font-weight: 600;
                        color: rgba(255,255,255,0.8) !important;
                        text-align: left;
                    }
                </style>
                <div class="address-form-container" style="display: grid; gap: 1rem; text-align: left; max-height: 70vh; overflow-y: auto; padding-right: 10px;">
                    <div>
                        <label>Address Label *</label>
                        <input id="swal-label" class="swal2-input" placeholder="e.g., Home, Work, Office" style="width: 100%; margin: 0;" required>
                    </div>
                    
                    <div>
                        <label>Region *</label>
                        <select id="swal-region" class="swal2-select" style="width: 100%; margin: 0;" required>
                            <option value="">Select Region</option>
                        </select>
                    </div>
                    
                    <div>
                        <label>Province *</label>
                        <select id="swal-province" class="swal2-select" style="width: 100%; margin: 0;" disabled required>
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    
                    <div>
                        <label>City *</label>
                        <select id="swal-city" class="swal2-select" style="width: 100%; margin: 0;" disabled required>
                            <option value="">Select City</option>
                        </select>
                    </div>
                    
                    <div>
                        <label>Barangay *</label>
                        <select id="swal-barangay" class="swal2-select" style="width: 100%; margin: 0;" disabled required>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    
                    <div>
                        <label>Street</label>
                        <textarea id="swal-street" class="swal2-textarea" placeholder="House No., Street Name, Subdivision, etc." style="width: 100%; margin: 0; min-height: 80px;"></textarea>
                    </div>
                </div>
            `,
            width: '600px',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonColor: '#e9b949',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Add Address',
            didOpen: () => {
                initializeAddressSelectors();
            },
            preConfirm: () => {
                const label = document.getElementById('swal-label').value;
                const regionSelect = document.getElementById('swal-region');
                const provinceSelect = document.getElementById('swal-province');
                const citySelect = document.getElementById('swal-city');
                const barangaySelect = document.getElementById('swal-barangay');
                const street = document.getElementById('swal-street').value;

                if (!label || !regionSelect.value || !provinceSelect.value || !citySelect.value || !barangaySelect.value) {
                    Swal.showValidationMessage('Please fill all required fields');
                    return false;
                }

                return {
                    label: label,
                    region_code: regionSelect.value,
                    region_name: regionSelect.options[regionSelect.selectedIndex].text,
                    province_code: provinceSelect.value,
                    province_name: provinceSelect.options[provinceSelect.selectedIndex].text,
                    city_code: citySelect.value,
                    city_name: citySelect.options[citySelect.selectedIndex].text,
                    barangay_code: barangaySelect.value,
                    barangay_name: barangaySelect.options[barangaySelect.selectedIndex].text,
                    street: street
                }
            }
        });

        if (formValues) {
            saveNewAddress(formValues);
        }
    }

    // Initialize address selectors
    function initializeAddressSelectors() {
        const regionSelect = $('#swal-region');
        const provinceSelect = $('#swal-province');
        const citySelect = $('#swal-city');
        const barangaySelect = $('#swal-barangay');

        regionSelect.on('change', function() {
            const regionCode = $(this).val();
            provinceSelect.prop('disabled', true).html('<option value="">Loading...</option>');
            citySelect.prop('disabled', true).html('<option value="">Select City</option>');
            barangaySelect.prop('disabled', true).html('<option value="">Select Barangay</option>');

            if (regionCode) {
                fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`)
                    .then(res => res.json())
                    .then(data => {
                        provinceSelect.html('<option value="">Select Province</option>');
                        data.forEach(item => {
                            provinceSelect.append(`<option value="${item.code}">${item.name}</option>`);
                        });
                        provinceSelect.prop('disabled', false);
                    });
            }
        });

        provinceSelect.on('change', function() {
            const provinceCode = $(this).val();
            citySelect.prop('disabled', true).html('<option value="">Loading...</option>');
            barangaySelect.prop('disabled', true).html('<option value="">Select Barangay</option>');

            if (provinceCode) {
                fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`)
                    .then(res => res.json())
                    .then(data => {
                        citySelect.html('<option value="">Select City</option>');
                        data.forEach(item => {
                            citySelect.append(`<option value="${item.code}">${item.name}</option>`);
                        });
                        citySelect.prop('disabled', false);
                    });
            }
        });

        citySelect.on('change', function() {
            const cityCode = $(this).val();
            barangaySelect.prop('disabled', true).html('<option value="">Loading...</option>');

            if (cityCode) {
                fetch(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`)
                    .then(res => res.json())
                    .then(data => {
                        barangaySelect.html('<option value="">Select Barangay</option>');
                        data.forEach(item => {
                            barangaySelect.append(`<option value="${item.code}">${item.name}</option>`);
                        });
                        barangaySelect.prop('disabled', false);
                    });
            }
        });

        // Load regions
        fetch('https://psgc.gitlab.io/api/regions/')
            .then(res => res.json())
            .then(data => {
                regionSelect.html('<option value="">Select Region</option>');
                data.forEach(item => {
                    regionSelect.append(`<option value="${item.code}">${item.name}</option>`);
                });
            });
    }

    // Save new address
    function saveNewAddress(data) {
        const formData = new FormData();
        formData.append('address_label', data.label);
        formData.append('street', data.street);
        formData.append('barangay_code', data.barangay_code);
        formData.append('barangay_name', data.barangay_name);
        formData.append('city_code', data.city_code);
        formData.append('city_name', data.city_name);
        formData.append('province_code', data.province_code);
        formData.append('province_name', data.province_name);
        formData.append('region_code', data.region_code);
        formData.append('region_name', data.region_name);

        fetch('manage_address.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Address added successfully!',
                    confirmButtonColor: '#e9b949'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to save address.',
                    confirmButtonColor: '#e9b949'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while saving the address.',
                confirmButtonColor: '#e9b949'
            });
        });
    }

    // Form validation
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

        const required = this.querySelectorAll('[required]');
        for (let field of required) {
            if (!field.value.trim()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Please complete all required fields!',
                    confirmButtonColor: '#e9b949'
                });
                return;
            }
        }
    });
    </script>
</body>
</html>