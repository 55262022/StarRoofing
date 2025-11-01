<?php
session_start();
require_once '../../database/starroofing_db.php';

// ✅ Require login
if (!isset($_SESSION['account_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please log in to add items to your cart.'
    ]);
    exit;
}

// ✅ Validate required fields
if (!isset($_POST['product_id'], $_POST['quantity'], $_POST['size'], $_POST['color'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields.'
    ]);
    exit;
}

$account_id = $_SESSION['account_id'];
$product_id = intval($_POST['product_id']);
$quantity   = max(1, intval($_POST['quantity']));
$size       = trim($_POST['size']);
$color      = trim($_POST['color']);

// ✅ Check if product exists
$productStmt = $conn->prepare("SELECT product_id, stock_quantity FROM products WHERE product_id = ?");
$productStmt->bind_param("i", $product_id);
$productStmt->execute();
$productResult = $productStmt->get_result();

if ($productResult->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Product not found.'
    ]);
    exit;
}

$product = $productResult->fetch_assoc();

// ✅ Check stock
if ($quantity > $product['stock_quantity']) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Not enough stock available.'
    ]);
    exit;
}

// ✅ Check if item already exists in cart
$checkStmt = $conn->prepare("
    SELECT cart_id, quantity 
    FROM cart 
    WHERE account_id = ? AND product_id = ? AND color = ? AND size = ?
");
$checkStmt->bind_param("iiss", $account_id, $product_id, $color, $size);
$checkStmt->execute();
$existing = $checkStmt->get_result()->fetch_assoc();

if ($existing) {
    // ✅ Update existing item quantity
    $newQuantity = $existing['quantity'] + $quantity;
    $updateStmt = $conn->prepare("
        UPDATE cart 
        SET quantity = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE cart_id = ?
    ");
    $updateStmt->bind_param("ii", $newQuantity, $existing['cart_id']);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    // ✅ Insert new cart item
    $insertStmt = $conn->prepare("
        INSERT INTO cart (account_id, product_id, color, size, quantity) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $insertStmt->bind_param("iissi", $account_id, $product_id, $color, $size, $quantity);
    $insertStmt->execute();
    $insertStmt->close();
}

echo json_encode([
    'status' => 'success',
    'message' => 'Item added to cart successfully.'
]);
exit;
?>
