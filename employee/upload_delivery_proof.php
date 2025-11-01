<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

requireEmployee();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

$order_id = intval($_POST['order_id']);
$employee_id = $_SESSION['employee_id'];

// Verify this order is assigned to this employee
$check_sql = "SELECT order_id, order_status FROM orders WHERE order_id = ? AND assigned_employee_id = ? AND order_status = 'shipped'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $order_id, $employee_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $_SESSION['error'] = "Order not found or you don't have permission to update it.";
    header("Location: dashboard.php");
    exit();
}

// Handle file upload
if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "Please upload a proof of delivery image.";
    header("Location: dashboard.php");
    exit();
}

$file = $_FILES['proof_image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    $_SESSION['error'] = "Invalid file type. Please upload a JPG, PNG, or GIF image.";
    header("Location: dashboard.php");
    exit();
}

// Validate file size
if ($file['size'] > $max_size) {
    $_SESSION['error'] = "File size too large. Maximum size is 5MB.";
    header("Location: dashboard.php");
    exit();
}

// Create upload directory if it doesn't exist
$upload_dir = '../uploads/delivery_proofs/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'proof_' . $order_id . '_' . time() . '.' . $file_extension;
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
    // Update order status and add delivery proof
    $delivery_proof_path = 'uploads/delivery_proofs/' . $filename;
    $update_sql = "UPDATE orders SET 
                   order_status = 'delivered', 
                   delivery_proof = ?,
                   delivered_at = NOW()
                   WHERE order_id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $delivery_proof_path, $order_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update order status.");
    }

    // Add to order status history
    $history_sql = "INSERT INTO order_status_history (order_id, status, notes, created_by) 
                    VALUES (?, 'delivered', 'Delivered by employee with proof of delivery', ?)";
    $history_stmt = $conn->prepare($history_sql);
    $history_stmt->bind_param("ii", $order_id, $employee_id);
    
    if (!$history_stmt->execute()) {
        throw new Exception("Failed to update order history.");
    }

    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = "Order marked as delivered successfully!";
    header("Location: dashboard.php");

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    // Delete uploaded file if transaction failed
    if (file_exists($target_path)) {
        unlink($target_path);
    }
    
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: dashboard.php");
}

exit();
?>