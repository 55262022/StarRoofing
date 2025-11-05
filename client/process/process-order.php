<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

// Function to generate unique order number
function generateOrderNumber($conn) {
    $prefix = 'ORD';
    $date = date('Ymd');
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit();
}

try {
    $conn->begin_transaction();

    $account_id = $_SESSION['account_id'];
    $is_cart_checkout = isset($_POST['is_cart_checkout']) && $_POST['is_cart_checkout'] == '1';

    // Common customer fields
    $address_id = intval($_POST['address_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'];
    $delivery_notes = trim($_POST['delivery_notes'] ?? '');

    // Fetch address
    $address_query = "SELECT * FROM user_addresses WHERE id = ? AND account_id = ?";
    $address_stmt = $conn->prepare($address_query);
    $address_stmt->bind_param("ii", $address_id, $account_id);
    $address_stmt->execute();
    $address = $address_stmt->get_result()->fetch_assoc();
    $address_stmt->close();

    if (!$address) {
        throw new Exception('Address not found.');
    }

    $delivery_fee = 150.00;
    $order_number = generateOrderNumber($conn);
    $payment_status = 'pending';

    // ===============================
    // 🛒 MULTIPLE ITEMS (from basket)
    // ===============================
    if ($is_cart_checkout) {
        if (!isset($_POST['cart_ids']) || !is_array($_POST['cart_ids'])) {
            throw new Exception('No cart items selected.');
        }

        $cart_ids = array_map('intval', $_POST['cart_ids']);
        $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
        $types = str_repeat('i', count($cart_ids));

        // Fetch selected cart items
        $cart_query = "
            SELECT c.cart_id, c.quantity, c.size, c.color, 
                   p.product_id, p.name, p.price, p.stock_quantity
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.cart_id IN ($placeholders) 
            AND c.account_id = ? 
            AND p.is_archived = 0
        ";
        
        $stmt = $conn->prepare($cart_query);
        $bind_params = array_merge($cart_ids, [$account_id]);
        $stmt->bind_param($types . 'i', ...$bind_params);
        $stmt->execute();
        $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($cart_items)) {
            throw new Exception('No valid items found in cart.');
        }

        // Calculate total for all items
        $grand_total = 0;
        foreach ($cart_items as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $grand_total += $item_total;
        }
        $grand_total += $delivery_fee;

        // Process each item
        foreach ($cart_items as $item) {
            if ($item['stock_quantity'] < $item['quantity']) {
                throw new Exception("Insufficient stock for {$item['name']}.");
            }

            $subtotal = $item['price'] * $item['quantity'];

            // Insert order for each item
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
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ";

            $insert_stmt = $conn->prepare($insert_order);
            $insert_stmt->bind_param(
                "sissssissssssisdidddss",
                $order_number,
                $account_id,
                $first_name,
                $last_name,
                $email,
                $phone,
                $address_id,
                $address['street'],
                $address['barangay_name'],
                $address['city_name'],
                $address['province_name'],
                $address['region_name'],
                $delivery_notes,
                $item['product_id'],
                $item['name'],
                $item['price'],
                $item['quantity'],
                $subtotal,
                $delivery_fee,
                $grand_total, // Use grand total for all items
                $payment_method,
                $payment_status
            );
            $insert_stmt->execute();
            $order_id = $conn->insert_id;
            $insert_stmt->close();

            // Add order history
            $history_stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'pending', 'Order placed')");
            $history_stmt->bind_param("i", $order_id);
            $history_stmt->execute();
            $history_stmt->close();

            // Update stock
            $stock_stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stock_stmt->execute();
            $stock_stmt->close();
        }

        // Remove purchased cart items
        $delete_cart = $conn->prepare("DELETE FROM cart WHERE cart_id IN ($placeholders)");
        $delete_cart->bind_param($types, ...$cart_ids);
        $delete_cart->execute();
        $delete_cart->close();

        $conn->commit();
        header("Location: ../pages/order-success.php?order_number=" . urlencode($order_number));
        exit();
    }

    // ===============================
    // 🛍 SINGLE ITEM CHECKOUT
    // ===============================
    else {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);

        $product_query = "SELECT product_id, name, price, stock_quantity FROM products WHERE product_id = ? AND is_archived = 0";
        $product_stmt = $conn->prepare($product_query);
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product = $product_stmt->get_result()->fetch_assoc();
        $product_stmt->close();

        if (!$product) {
            throw new Exception('Product not found.');
        }

        if ($product['stock_quantity'] < $quantity) {
            throw new Exception('Insufficient stock.');
        }

        $subtotal = $product['price'] * $quantity;
        $total = $subtotal + $delivery_fee;

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
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";

        $stmt = $conn->prepare($insert_order);
        $stmt->bind_param(
            "sissssissssssisdidddss",
            $order_number,
            $account_id,
            $first_name,
            $last_name,
            $email,
            $phone,
            $address_id,
            $address['street'],
            $address['barangay_name'],
            $address['city_name'],
            $address['province_name'],
            $address['region_name'],
            $delivery_notes,
            $product_id,
            $product['name'],
            $product['price'],
            $quantity,
            $subtotal,
            $delivery_fee,
            $total,
            $payment_method,
            $payment_status
        );
        $stmt->execute();
        $order_id = $conn->insert_id;
        $stmt->close();

        $history_stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'pending', 'Order placed')");
        $history_stmt->bind_param("i", $order_id);
        $history_stmt->execute();
        $history_stmt->close();

        $stock_stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
        $stock_stmt->bind_param("ii", $quantity, $product_id);
        $stock_stmt->execute();
        $stock_stmt->close();

        $conn->commit();
        header("Location: ../pages/order-success.php?order_number=" . urlencode($order_number));
        exit();
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("Order processing error: " . $e->getMessage());
    $_SESSION['checkout_error'] = $e->getMessage();
    header("Location: checkout.php");
    exit();
}
?>