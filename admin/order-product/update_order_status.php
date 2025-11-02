<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

// Check if admin
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'], $_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$order_id = intval($_POST['order_id']);
$new_status = $_POST['status'];

// Validate status
$valid_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    // Get current order status and assignment
    $check_query = "SELECT order_status, assigned_employee_id FROM orders WHERE order_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $current_order = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if (!$current_order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }

    $current_status = $current_order['order_status'];
    $assigned_employee_id = $current_order['assigned_employee_id'];

    // Define valid status transitions for ADMIN
    $valid_transitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['cancelled'], // Admin cannot ship from processing - must assign employee
        'shipped' => [], // Only employee can mark as delivered
        'delivered' => [], // No transitions allowed from delivered
        'cancelled' => []  // No transitions allowed from cancelled
    ];

    // Validate transition
    if (!in_array($new_status, $valid_transitions[$current_status])) {
        $error_messages = [
            'processing' => 'To ship this order, please assign a delivery employee using the "Assign Employee & Ship" button.',
            'shipped' => 'Only the assigned delivery employee can mark this order as delivered.',
            'delivered' => 'This order is already delivered and cannot be modified.',
            'cancelled' => 'Cancelled orders cannot be modified.'
        ];

        $message = $error_messages[$current_status] ?? "Cannot change status from '{$current_status}' to '{$new_status}'";
        
        echo json_encode([
            'success' => false, 
            'message' => $message
        ]);
        exit();
    }

    $conn->begin_transaction();

    // Update order status
    $update_query = "UPDATE orders SET order_status = ?, updated_at = CURRENT_TIMESTAMP";
    
    // Update timestamp fields based on status
    if ($new_status === 'confirmed') {
        $update_query .= ", confirmed_at = CURRENT_TIMESTAMP";
    } elseif ($new_status === 'delivered') {
        $update_query .= ", delivered_at = CURRENT_TIMESTAMP, payment_status = 'paid'";
    }
    
    $update_query .= " WHERE order_id = ?";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();

    // Add to status history
    $history_query = "INSERT INTO order_status_history (order_id, status, created_by, notes) VALUES (?, ?, ?, ?)";
    $notes = "Status updated from " . ucfirst($current_status) . " to " . ucfirst($new_status) . " by admin";
    $history_stmt = $conn->prepare($history_query);
    $history_stmt->bind_param("isis", $order_id, $new_status, $_SESSION['account_id'], $notes);
    $history_stmt->execute();
    $history_stmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Order status updated successfully',
        'new_status' => $new_status,
        'old_status' => $current_status
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()]);
}
?>