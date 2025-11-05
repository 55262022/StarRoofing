<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Require admin access
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock_product'])) {
    $product_id = intval($_POST['product_id']);
    $restock_quantity = intval($_POST['restock_quantity']);
    
    // Validate restock quantity (must be positive)
    if ($restock_quantity <= 0) {
        header("Location: ../admin/inventory.php?error=" . urlencode("Restock quantity must be greater than 0"));
        exit();
    }
    
    // Get current stock quantity
    $stmt = $conn->prepare("SELECT stock_quantity, name FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $current_stock = $product['stock_quantity'];
        $product_name = $product['name'];
        
        // Calculate new stock (add restock quantity to current stock)
        $new_stock = $current_stock + $restock_quantity;
        
        // Update product stock
        $update_stmt = $conn->prepare("UPDATE products SET stock_quantity = ?, updated_at = NOW() WHERE product_id = ?");
        $update_stmt->bind_param("ii", $new_stock, $product_id);
        
        if ($update_stmt->execute()) {
            header("Location: ../admin/inventory.php?success=" . urlencode("Successfully restocked $product_name. Added $restock_quantity units. New stock: $new_stock"));
            exit();
        } else {
            header("Location: ../admin/inventory.php?error=" . urlencode("Failed to restock product"));
            exit();
        }
    } else {
        header("Location: ../admin/inventory.php?error=" . urlencode("Product not found"));
        exit();
    }
} else {
    header("Location: ../admin/inventory.php");
    exit();
}
?>