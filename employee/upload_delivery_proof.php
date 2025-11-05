<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

requireEmployee();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: dashboard.php");
    exit();
}

$order_id = intval($_POST['order_id'] ?? 0);
$employee_id = $_SESSION['employee_id'] ?? null;

if (!$employee_id) {
    $_SESSION['error'] = "Employee session not found. Please login again.";
    header("Location: ../public/login.php");
    exit();
}

if ($order_id <= 0) {
    $_SESSION['error'] = "Invalid order ID.";
    header("Location: dashboard.php");
    exit();
}

// Verify this order is assigned to this employee and in to_ship status
$check_sql = "SELECT order_id, order_number, order_status FROM orders 
              WHERE order_id = ? AND assigned_employee_id = ? AND order_status = 'to_ship'";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    $_SESSION['error'] = "Database error occurred.";
    header("Location: dashboard.php");
    exit();
}

$check_stmt->bind_param("ii", $order_id, $employee_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $_SESSION['error'] = "Order not found, not assigned to you, or already delivered.";
    header("Location: dashboard.php");
    exit();
}

$order = $check_result->fetch_assoc();
$check_stmt->close();

// Handle file upload
if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
    $error_message = "Please upload a proof of delivery image.";
    
    if (isset($_FILES['proof_image']['error'])) {
        switch ($_FILES['proof_image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = "File size exceeds maximum allowed size.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = "File upload was interrupted. Please try again.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = "No file was uploaded.";
                break;
        }
    }
    
    $_SESSION['error'] = $error_message;
    header("Location: dashboard.php");
    exit();
}

$file = $_FILES['proof_image'];

// Validate file type using finfo for better security
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    $_SESSION['error'] = "Invalid file type. Please upload a JPG, PNG, or GIF image.";
    header("Location: dashboard.php");
    exit();
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    $_SESSION['error'] = "File size too large. Maximum size is 5MB.";
    header("Location: dashboard.php");
    exit();
}

// Validate it's actually an image
$image_info = getimagesize($file['tmp_name']);
if ($image_info === false) {
    $_SESSION['error'] = "Invalid image file.";
    header("Location: dashboard.php");
    exit();
}

// Create upload directory if it doesn't exist
$upload_dir = '../uploads/delivery_proofs/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        $_SESSION['error'] = "Failed to create upload directory.";
        header("Location: dashboard.php");
        exit();
    }
}

// Generate unique filename with additional random component
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = 'delivery_proof_' . $order_id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
$target_path = $upload_dir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    $_SESSION['error'] = "Failed to upload file. Please try again.";
    header("Location: dashboard.php");
    exit();
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
                   WHERE order_id = ? AND order_status = 'to_ship'";
    
    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) {
        throw new Exception("Database prepare error.");
    }
    
    $update_stmt->bind_param("si", $filename, $order_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update order status.");
    }
    
    if ($update_stmt->affected_rows === 0) {
        throw new Exception("Order was not updated. It may have been modified by another user.");
    }
    
    $update_stmt->close();

    // Get employee account_id for history
    $emp_account_sql = "SELECT account_id, first_name, last_name FROM employees WHERE employee_id = ?";
    $emp_account_stmt = $conn->prepare($emp_account_sql);
    
    if (!$emp_account_stmt) {
        throw new Exception("Database prepare error.");
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
        throw new Exception("Database prepare error.");
    }
    
    $history_stmt->bind_param("isi", $order_id, $history_notes, $created_by);
    
    if (!$history_stmt->execute()) {
        throw new Exception("Failed to update order history.");
    }
    
    $history_stmt->close();

    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = "Order #{$order['order_number']} marked as delivered successfully!";
    header("Location: dashboard.php");

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    // Delete uploaded file if transaction failed
    if (file_exists($target_path)) {
        unlink($target_path);
    }
    
    // Log error
    error_log("Delivery proof upload error for order $order_id: " . $e->getMessage());
    
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: dashboard.php");
}

exit();
?>