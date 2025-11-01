<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

header('Content-Type: application/json');

// Check if admin
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$order_id = intval($_POST['order_id'] ?? 0);
$employee_id = intval($_POST['employee_id'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

// Validate inputs
if (empty($order_id) || empty($employee_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID and Employee ID are required']);
    exit();
}

try {
    // Verify employee exists and is not archived
    $emp_check = "SELECT employee_id, first_name, last_name FROM employees WHERE employee_id = ? AND is_archived = 0";
    $emp_stmt = $conn->prepare($emp_check);
    $emp_stmt->bind_param("i", $employee_id);
    $emp_stmt->execute();
    $emp_result = $emp_stmt->get_result();

    if ($emp_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Selected employee is not available']);
        exit();
    }

    $employee = $emp_result->fetch_assoc();
    $emp_stmt->close();

    // Verify order exists and is in correct status
    $order_check = "SELECT order_id, order_number, order_status FROM orders WHERE order_id = ?";
    $order_stmt = $conn->prepare($order_check);
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }

    $order = $order_result->fetch_assoc();
    $order_stmt->close();

    // Start transaction
    $conn->begin_transaction();

    // Update order with assigned employee
    $update_sql = "UPDATE orders SET 
                   assigned_employee_id = ?,
                   updated_at = NOW()
                   WHERE order_id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $employee_id, $order_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to assign employee to order");
    }
    $update_stmt->close();

    // Add to order status history
    $history_notes = "Order assigned to employee: {$employee['first_name']} {$employee['last_name']}";
    if (!empty($notes)) {
        $history_notes .= "\nInstructions: " . $notes;
    }
    
    $history_sql = "INSERT INTO order_status_history (order_id, status, notes, created_by) 
                    VALUES (?, ?, ?, ?)";
    $history_stmt = $conn->prepare($history_sql);
    $current_status = $order['order_status'];
    $admin_id = $_SESSION['account_id'];
    $history_stmt->bind_param("issi", $order_id, $current_status, $history_notes, $admin_id);
    
    if (!$history_stmt->execute()) {
        throw new Exception("Failed to update order history");
    }
    $history_stmt->close();

    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Order successfully assigned to {$employee['first_name']} {$employee['last_name']}!"
    ]);

} catch (Exception $e) {
    // Rollback on error
    if ($conn->ping()) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>