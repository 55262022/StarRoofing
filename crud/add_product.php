<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $category_id    = $_POST['category_id'];
    $name           = $_POST['name'];
    $description    = $_POST['description'];
    $price          = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $unit           = $_POST['unit'];
    $created_by     = $_SESSION['account_id'];

    $image_path = null;

    // Handle file upload
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

    // Prepare insert
    $sql = "INSERT INTO products 
        (category_id, name, description, price, stock_quantity, unit, image_path, created_by, created_at, updated_at, is_archived) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 0)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param(
        "issdissi",
        $category_id,
        $name,
        $description,
        $price,
        $stock_quantity,
        $unit,
        $image_path,
        $created_by
    );

    if ($stmt->execute()) {
        header("Location: ../admin/inventory.php?success=Product added successfully");
    } else {
        header("Location: ../admin/inventory.php?error=Failed to add product: " . urlencode($stmt->error));
    }
}
