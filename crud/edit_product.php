<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Require admin
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id     = intval($_POST['product_id']);
    $category_id    = intval($_POST['category_id']);
    $name           = trim($_POST['name']);
    $description    = trim($_POST['description']);
    $price          = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $unit           = trim($_POST['unit']);

    $image_path = null;

    // Handle file upload if a new image is provided
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/products/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename    = time() . "_" . basename($_FILES['image_file']['name']);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
            $image_path = "uploads/products/" . $filename;
        }
    }

    // Update with or without image
    if ($image_path) {
        $sql = "UPDATE products 
                SET category_id=?, name=?, description=?, price=?, stock_quantity=?, unit=?, image_path=?, updated_at=NOW()
                WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            header("Location: ../admin/inventory.php?error=" . urlencode("Database error: " . $conn->error));
            exit();
        }
        
        // FIXED: "issdissi" (was "issdissi" with wrong order)
        $stmt->bind_param("issdissi", $category_id, $name, $description, $price, $stock_quantity, $unit, $image_path, $product_id);
    } else {
        $sql = "UPDATE products 
                SET category_id=?, name=?, description=?, price=?, stock_quantity=?, unit=?, updated_at=NOW()
                WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            header("Location: ../admin/inventory.php?error=" . urlencode("Database error: " . $conn->error));
            exit();
        }
        
        // FIXED: "issdisi" (was "issdisi")
        $stmt->bind_param("issdisi", $category_id, $name, $description, $price, $stock_quantity, $unit, $product_id);
    }

    if ($stmt->execute()) {
        header("Location: ../admin/inventory.php?success=Product updated successfully");
        exit();
    } else {
        header("Location: ../admin/inventory.php?error=" . urlencode("Failed to update: " . $stmt->error));
        exit();
    }
} else {
    header("Location: ../admin/inventory.php");
    exit();
}
?>