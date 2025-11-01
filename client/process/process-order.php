<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

// Function to generate unique order number
function generateOrderNumber($conn) {
    $prefix = 'ORD';
    $date = date('Ymd');
    
    // Get the last order number for today
    $query = "SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY order_id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $pattern = $prefix . $date . '%';
    $stmt->bind_param("s", $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastNumber = intval(substr($row['order_number'], -4));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    $orderNumber = $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    $stmt->close();
    
    return $orderNumber;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get and validate form data
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $address_id = intval($_POST['address_id']);
    
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'];
    $delivery_notes = trim($_POST['delivery_notes'] ?? '');
    
    // Validate required fields
    if (empty($product_id) || empty($quantity) || empty($address_id) || 
        empty($first_name) || empty($last_name) || empty($email) || 
        empty($phone) || empty($payment_method)) {
        throw new Exception('Please fill in all required fields.');
    }
    
    // Get product details
    $product_query = "SELECT product_id, name, price, stock_quantity FROM products WHERE product_id = ? AND is_archived = 0";
    $product_stmt = $conn->prepare($product_query);
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product = $product_stmt->get_result()->fetch_assoc();
    $product_stmt->close();
    
    if (!$product) {
        throw new Exception('Product not found or unavailable.');
    }
    
    // Check stock availability
    if ($product['stock_quantity'] < $quantity) {
        throw new Exception('Insufficient stock. Available: ' . $product['stock_quantity']);
    }
    
    // Get address details
    $address_query = "SELECT * FROM user_addresses WHERE id = ? AND account_id = ?";
    $address_stmt = $conn->prepare($address_query);
    $address_stmt->bind_param("ii", $address_id, $_SESSION['account_id']);
    $address_stmt->execute();
    $address = $address_stmt->get_result()->fetch_assoc();
    $address_stmt->close();
    
    if (!$address) {
        throw new Exception('Address not found.');
    }
    
    // Calculate totals
    $subtotal = $product['price'] * $quantity;
    $delivery_fee = 150.00;
    $total = $subtotal + $delivery_fee;
    
    // Generate unique order number
    $order_number = generateOrderNumber($conn);
    
    // Set payment status based on payment method
    $payment_status = ($payment_method === 'cod') ? 'pending' : 'pending';
    
    $order_status;
    
    // Insert order
    $insert_order = "
        INSERT INTO orders (
            order_number, account_id, 
            customer_first_name, customer_last_name, customer_email, customer_phone,
            address_id, delivery_street, delivery_barangay, delivery_city, 
            delivery_province, delivery_region, delivery_notes,
            product_id, product_name, product_price, quantity,
            subtotal, delivery_fee, total_amount,
            payment_method, payment_status
        ) VALUES (
            ?, ?, 
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?
        )
    ";

    $stmt = $conn->prepare($insert_order);
    
    // Count: 22 placeholders
    // s = string, i = integer, d = decimal/double
    $stmt->bind_param(
        "sissssissssssisdidddss",
        $order_number,              // s
        $_SESSION['account_id'],    // i
        $first_name,                // s
        $last_name,                 // s
        $email,                     // s
        $phone,                     // s
        $address_id,                // i
        $address['street'],         // s
        $address['barangay_name'],  // s
        $address['city_name'],      // s
        $address['province_name'],  // s
        $address['region_name'],    // s
        $delivery_notes,            // s
        $product_id,                // i
        $product['name'],           // s
        $product['price'],          // d
        $quantity,                  // i
        $subtotal,                  // d
        $delivery_fee,              // d
        $total,                     // d
        $payment_method,            // s
        $payment_status             // s
    );
   
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order.');
    }
    
    $order_id = $conn->insert_id;
    $stmt->close();
    
    // Insert initial status history
    $history_query = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'pending', 'Order placed')";
    $history_stmt = $conn->prepare($history_query);
    $history_stmt->bind_param("i", $order_id);
    $history_stmt->execute();
    $history_stmt->close();
    
    // Update product stock
    $update_stock = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
    $stock_stmt = $conn->prepare($update_stock);
    $stock_stmt->bind_param("ii", $quantity, $product_id);
    $stock_stmt->execute();
    $stock_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Redirect to success page with order number
    header("Location: ../pages/order-success.php?order_number=" . urlencode($order_number));
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log error (optional)
    error_log("Order processing error: " . $e->getMessage());
    
    // Redirect back to checkout with error
    $_SESSION['checkout_error'] = $e->getMessage();
    header("Location: checkout.php?product_id=" . $product_id . "&quantity=" . $quantity);
    exit();
}
?>