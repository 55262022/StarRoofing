<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Set JSON response header
header('Content-Type: application/json');

// Function to send JSON response
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Check authentication
try {
    requireEmployee();
} catch (Exception $e) {
    sendResponse(false, 'Authentication required. Please login again.');
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

// Get and validate employee_id
$employee_id = $_SESSION['employee_id'] ?? null;
if (!$employee_id) {
    sendResponse(false, 'Employee session not found. Please login again.');
}

// Get and validate order_id
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
if ($order_id <= 0) {
    sendResponse(false, 'Invalid order ID.');
}

// Verify this order is assigned to this employee and in to_ship status
$check_sql = "SELECT o.order_id, o.order_number, o.order_status, o.customer_first_name, o.customer_last_name
              FROM orders o
              WHERE o.order_id = ? AND o.assigned_employee_id = ? AND o.order_status = 'to_ship'";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    sendResponse(false, 'Database error: ' . $conn->error);
}

$check_stmt->bind_param("ii", $order_id, $employee_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    sendResponse(false, 'Order not found, not assigned to you, or already delivered.');
}

$order = $check_result->fetch_assoc();
$check_stmt->close();

// Validate file upload
if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
    $error_message = 'Please upload a proof of delivery image.';
    
    if (isset($_FILES['proof_image']['error'])) {
        switch ($_FILES['proof_image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = 'File size exceeds maximum allowed size.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = 'File upload was interrupted. Please try again.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = 'No file was uploaded.';
                break;
            default:
                $error_message = 'File upload error occurred.';
        }
    }
    
    sendResponse(false, $error_message);
}

$file = $_FILES['proof_image'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    sendResponse(false, 'Invalid file type. Please upload a JPG, PNG, or GIF image.');
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    sendResponse(false, 'File size too large. Maximum size is 5MB.');
}

// Validate image dimensions (optional - prevent extremely large images)
$image_info = getimagesize($file['tmp_name']);
if ($image_info === false) {
    sendResponse(false, 'Invalid image file.');
}

// Create upload directory if it doesn't exist
$upload_dir = '../uploads/delivery_proofs/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        sendResponse(false, 'Failed to create upload directory.');
    }
}

// Generate unique filename
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = 'delivery_proof_' . $order_id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
$target_path = $upload_dir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    sendResponse(false, 'Failed to save uploaded file. Please try again.');
}

// Start transaction
$conn->begin_transaction();

try {
    // Update order status to delivered with delivery proof
    $update_sql = "UPDATE orders SET 
                   order_status = 'delivered', 
                   delivery_proof = ?,
                   delivered_at = NOW(),
                   payment_status = 'paid',
                   updated_at = NOW()
                   WHERE order_id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $update_stmt->bind_param("si", $filename, $order_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update order status: " . $update_stmt->error);
    }
    
    if ($update_stmt->affected_rows === 0) {
        throw new Exception("Order was not updated. It may have been modified by another user.");
    }
    
    $update_stmt->close();

    // Get employee account_id for history
    $emp_account_sql = "SELECT account_id, first_name, last_name FROM employees WHERE employee_id = ?";
    $emp_account_stmt = $conn->prepare($emp_account_sql);
    
    if (!$emp_account_stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $emp_account_stmt->bind_param("i", $employee_id);
    $emp_account_stmt->execute();
    $emp_account_result = $emp_account_stmt->get_result();
    $emp_account = $emp_account_result->fetch_assoc();
    $created_by = $emp_account['account_id'] ?? null;
    $employee_name = ($emp_account['first_name'] ?? '') . ' ' . ($emp_account['last_name'] ?? '');
    $emp_account_stmt->close();

    // Add to order status history
    $history_notes = "Order delivered by " . trim($employee_name) . " with proof of delivery uploaded.";
    $history_sql = "INSERT INTO order_status_history (order_id, status, notes, created_by, created_at) 
                    VALUES (?, 'delivered', ?, ?, NOW())";
    $history_stmt = $conn->prepare($history_sql);
    
    if (!$history_stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $history_stmt->bind_param("isi", $order_id, $history_notes, $created_by);
    
    if (!$history_stmt->execute()) {
        throw new Exception("Failed to update order history: " . $history_stmt->error);
    }
    
    $history_stmt->close();

    // Commit transaction
    $conn->commit();
    
    // Prepare response data
    $response_data = [
        'order_id' => $order_id,
        'order_number' => $order['order_number'],
        'proof_filename' => $filename,
        'proof_url' => '/STARROOFING/uploads/delivery_proofs/' . $filename,
        'delivered_date' => date('M j, Y'),
        'customer_name' => $order['customer_first_name'] . ' ' . $order['customer_last_name']
    ];
    
    sendResponse(true, "Order #{$order['order_number']} marked as delivered successfully!", $response_data);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    // Delete uploaded file if transaction failed
    if (file_exists($target_path)) {
        unlink($target_path);
    }
    
    // Log error (optional - you can add file logging here)
    error_log("Delivery proof upload error for order $order_id: " . $e->getMessage());
    
    sendResponse(false, $e->getMessage());
}
?>