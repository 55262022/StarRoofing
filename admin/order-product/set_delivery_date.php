<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';

// Prevent any output before JSON
ob_start();

requireAuth();

// Check if admin
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$order_id = intval($_POST['order_id'] ?? 0);
$expected_delivery_date = trim($_POST['expected_delivery_date'] ?? '');
$delivery_notes = trim($_POST['delivery_notes'] ?? '');

// Validate inputs
if (empty($order_id) || empty($expected_delivery_date)) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Order ID and delivery date are required']);
    exit();
}

// Validate date format
$date = DateTime::createFromFormat('Y-m-d', $expected_delivery_date);
if (!$date || $date->format('Y-m-d') !== $expected_delivery_date) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit();
}

// Check if date is not in the past
$today = new DateTime();
$today->setTime(0, 0, 0);
if ($date < $today) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Delivery date cannot be in the past']);
    exit();
}

try {
    // Verify order exists and is in to_ship status
    $order_check = "SELECT order_id, order_number, order_status FROM orders WHERE order_id = ?";
    $order_stmt = $conn->prepare($order_check);
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows === 0) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }

    $order = $order_result->fetch_assoc();
    
    // Check if order is in to_ship status
    if ($order['order_status'] !== 'to_ship') {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Can only set delivery date for orders ready to ship']);
        exit();
    }
    
    $order_stmt->close();

    // Start transaction
    $conn->begin_transaction();

    // Check if expected_delivery_date column exists
    $column_check = $conn->query("SHOW COLUMNS FROM orders LIKE 'expected_delivery_date'");
    $has_delivery_date = $column_check->num_rows > 0;

    if (!$has_delivery_date) {
        // Add the column if it doesn't exist
        $alter_query = "ALTER TABLE orders ADD COLUMN expected_delivery_date DATE NULL AFTER assigned_at";
        $conn->query($alter_query);
    }

    // Update order with expected delivery date
    $update_sql = "UPDATE orders SET 
                   expected_delivery_date = ?,
                   updated_at = NOW()
                   WHERE order_id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $expected_delivery_date, $order_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to set delivery date");
    }
    $update_stmt->close();

    // Add to order status history
    $formatted_date = date('F j, Y', strtotime($expected_delivery_date));
    $history_notes = "Expected delivery date set to: {$formatted_date}";
    if (!empty($delivery_notes)) {
        $history_notes .= " | Notes: " . $delivery_notes;
    }
    
    $history_sql = "INSERT INTO order_status_history (order_id, status, notes, created_by) 
                    VALUES (?, 'to_ship', ?, ?)";
    $history_stmt = $conn->prepare($history_sql);
    $admin_id = $_SESSION['account_id'];
    $history_stmt->bind_param("isi", $order_id, $history_notes, $admin_id);
    
    if (!$history_stmt->execute()) {
        throw new Exception("Failed to update order history");
    }
    $history_stmt->close();

    // Commit transaction
    $conn->commit();
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => "Expected delivery date set to {$formatted_date} for order #{$order['order_number']}"
    ]);

} catch (Exception $e) {
    // Rollback on error
    if ($conn->ping()) {
        $conn->rollback();
    }
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>