<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    // Update the product to set is_archived to 0
    $sql = "UPDATE products SET is_archived = 0 WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        // Get product name for success message
        $name_sql = "SELECT name FROM products WHERE product_id = ?";
        $name_stmt = $conn->prepare($name_sql);
        $name_stmt->bind_param("i", $product_id);
        $name_stmt->execute();
        $name_result = $name_stmt->get_result();
        $product = $name_result->fetch_assoc();
        
        $_SESSION['restore_success'] = "Product '{$product['name']}' has been successfully restored!";
    } else {
        $_SESSION['restore_error'] = "Error restoring product: " . $conn->error;
    }
    
    $stmt->close();
    $name_stmt->close();
    header("Location: ../admin/archive.php");
    exit();
}
?>