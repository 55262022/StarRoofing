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
$employee_id = intval($_POST['employee_id'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

// Validate inputs
if (empty($order_id) || empty($employee_id)) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Order ID and Employee ID are required']);
    exit();
}

try {
    // Verify employee exists, is not archived, and is in Logistics department
    $emp_check = "SELECT employee_id, first_name, last_name, department 
                  FROM employees 
                  WHERE employee_id = ? AND is_archived = 0 AND department = 'Logistics and Services'";
    $emp_stmt = $conn->prepare($emp_check);
    $emp_stmt->bind_param("i", $employee_id);
    $emp_stmt->execute();
    $emp_result = $emp_stmt->get_result();

    if ($emp_result->num_rows === 0) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Selected employee is not available or not from Logistics and Services department']);
        exit();
    }

    $employee = $emp_result->fetch_assoc();
    $emp_stmt->close();

    // Verify order exists and is in confirmed status
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
    
    // Check if order is in confirmed or to_ship status (allow reassignment)
    if (!in_array($order['order_status'], ['confirmed', 'to_ship'])) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order must be in confirmed or to_ship status to assign employee']);
        exit();
    }
    
    $order_stmt->close();

    // Start transaction
    $conn->begin_transaction();

    // Check if assigned_at column exists
    $column_check = $conn->query("SHOW COLUMNS FROM orders LIKE 'assigned_at'");
    $has_assigned_at = $column_check->num_rows > 0;

    // Update order: assign employee AND change status to 'to_ship'
    if ($has_assigned_at) {
        $update_sql = "UPDATE orders SET 
                       assigned_employee_id = ?,
                       order_status = 'to_ship',
                       assigned_at = NOW(),
                       updated_at = NOW()
                       WHERE order_id = ?";
    } else {
        // Fallback if column doesn't exist - use updated_at
        $update_sql = "UPDATE orders SET 
                       assigned_employee_id = ?,
                       order_status = 'to_ship',
                       updated_at = NOW()
                       WHERE order_id = ?";
    }
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $employee_id, $order_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to assign employee and update order status");
    }
    $update_stmt->close();

    // Add to order status history
    $history_notes = "Order ready to ship. Assigned to delivery employee: {$employee['first_name']} {$employee['last_name']}";
    if (!empty($notes)) {
        $history_notes .= " | Delivery Instructions: " . $notes;
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
        'message' => "Order #{$order['order_number']} has been assigned to {$employee['first_name']} {$employee['last_name']} and is ready to ship!"
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