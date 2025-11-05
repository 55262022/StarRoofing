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
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Validate status - only confirmed and cancelled allowed from admin
$valid_statuses = ['confirmed', 'cancelled'];
if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status. Only confirm or decline actions are allowed.']);
    exit();
}

try {
    // Get current order status
    $check_query = "SELECT order_status, order_number FROM orders WHERE order_id = ?";
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
    $order_number = $current_order['order_number'];

    // Only allow status changes from 'pending'
    if ($current_status !== 'pending') {
        $status_messages = [
            'confirmed' => 'This order has already been confirmed.',
            'to_ship' => 'This order is ready to ship and cannot be modified.',
            'delivered' => 'This order has been delivered and cannot be modified.',
            'cancelled' => 'This order has already been cancelled.'
        ];
        
        $message = $status_messages[$current_status] ?? "Cannot modify order with status: {$current_status}";
        
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
    }
    
    $update_query .= " WHERE order_id = ?";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();

    // Prepare history notes
    if ($new_status === 'confirmed') {
        $history_notes = "Order confirmed by admin";
    } else { // cancelled
        $history_notes = "Order declined/cancelled by admin";
        if (!empty($notes)) {
            $history_notes .= " | Reason: " . $notes;
        }
    }

    // Add to status history
    $history_query = "INSERT INTO order_status_history (order_id, status, created_by, notes) VALUES (?, ?, ?, ?)";
    $history_stmt = $conn->prepare($history_query);
    $history_stmt->bind_param("isis", $order_id, $new_status, $_SESSION['account_id'], $history_notes);
    $history_stmt->execute();
    $history_stmt->close();

    $conn->commit();

    // Prepare success message
    if ($new_status === 'confirmed') {
        $success_message = "Order #{$order_number} has been confirmed successfully! You can now assign a delivery employee.";
    } else {
        $success_message = "Order #{$order_number} has been declined and cancelled.";
    }

    echo json_encode([
        'success' => true, 
        'message' => $success_message,
        'new_status' => $new_status,
        'old_status' => $current_status
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()]);
}
?>