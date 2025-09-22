<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id     = intval($_POST['product_id']);
    $category_id    = $_POST['category_id'];
    $name           = $_POST['name'];
    $description    = $_POST['description'];
    $price          = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $unit           = $_POST['unit'];

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

    if ($image_path) {
        $sql = "UPDATE products 
                   SET category_id=?, name=?, description=?, price=?, stock_quantity=?, unit=?, image_path=? 
                 WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("SQL error: " . $conn->error);
        }
        $stmt->bind_param("issdissi", $category_id, $name, $description, $price, $stock_quantity, $unit, $image_path, $product_id);
    } else {
        $sql = "UPDATE products 
                   SET category_id=?, name=?, description=?, price=?, stock_quantity=?, unit=? 
                 WHERE product_id=?";
        $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: ../admin/inventory.php?error=" . urlencode("SQL error: " . $conn->error));
        exit();
    }
        $stmt->bind_param("issdisi", $category_id, $name, $description, $price, $stock_quantity, $unit, $product_id);
    }

    if ($stmt->execute()) {
        header("Location: ../admin/inventory.php?success=Product updated successfully");
        exit();
    } else {
        header("Location: ../admin/inventory.php?error=Failed to update product: " . $stmt->error);
        exit();
    }
} else {
    header("Location: ../admin/inventory.php");
    exit();
}
