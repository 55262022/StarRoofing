<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Require authentication and admin access
requireAdmin();

// Debug: Check session
if (!isset($_SESSION['account_id']) || empty($_SESSION['account_id'])) {
    error_log("Session account_id is empty!");
    header("Location: ../admin/inventory.php?error=Authentication error. Please log in again.");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $category_id    = $_POST['category_id'];
    $name           = trim($_POST['name']);
    $description    = $_POST['description'];
    $price          = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $unit           = $_POST['unit'];
    
    // Use helper function
    $created_by     = getCurrentUserId();

    // Extra validation
    if (empty($created_by)) {
        error_log("created_by is empty! Session: " . print_r($_SESSION, true));
        header("Location: ../admin/inventory.php?error=Session expired. Please log in again.");
        exit();
    }

    error_log("Adding product - created_by: $created_by, name: $name");

    $image_path = null;

    // Handle image upload
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

    // Check if product already exists
    $check_sql = "SELECT product_id, stock_quantity FROM products WHERE name = ? AND category_id = ? AND is_archived = 0";
    $check_stmt = $conn->prepare($check_sql);
    
    if (!$check_stmt) {
        error_log("Check SQL error: " . $conn->error);
        header("Location: ../admin/inventory.php?error=Database error");
        exit();
    }

    $check_stmt->bind_param("si", $name, $category_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Product exists — update quantity
        $existing_product = $result->fetch_assoc();
        $new_quantity = $existing_product['stock_quantity'] + $stock_quantity;

        $update_sql = "UPDATE products
                        SET stock_quantity = ?, updated_at = NOW()
                        WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        
        if (!$update_stmt) {
            error_log("Update SQL error: " . $conn->error);
            header("Location: ../admin/inventory.php?error=Database error");
            exit();
        }

        $update_stmt->bind_param("ii", $new_quantity, $existing_product['product_id']);

        if ($update_stmt->execute()) {
            header("Location: ../admin/inventory.php?success=Quantity updated successfully");
            exit();
        } else {
            error_log("Update execution error: " . $update_stmt->error);
            header("Location: ../admin/inventory.php?error=Failed to update quantity");
            exit();
        }

    } else {
        // Product does not exist — insert new one
        $insert_sql = "INSERT INTO products 
            (category_id, name, description, price, stock_quantity, unit, image_path, created_by, created_at, updated_at, is_archived)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 0)";
                
        $insert_stmt = $conn->prepare($insert_sql);
        
        if (!$insert_stmt) {
            error_log("Insert SQL error: " . $conn->error);
            header("Location: ../admin/inventory.php?error=Database error: " . urlencode($conn->error));
            exit();
        }

        $insert_stmt->bind_param(
            "issdissi",        // 8 type specifiers
            $category_id,      // i - integer
            $name,             // s - string
            $description,      // s - string
            $price,            // d - decimal
            $stock_quantity,   // i - integer
            $unit,             // s - string
            $image_path,       // s - string
            $created_by        // i - integer
        );

        error_log("Executing insert with created_by: $created_by");

        if ($insert_stmt->execute()) {
            error_log("Product inserted successfully");
            header("Location: ../admin/inventory.php?success=Product added successfully");
            exit();
        } else {
            error_log("Insert execution error: " . $insert_stmt->error);
            header("Location: ../admin/inventory.php?error=" . urlencode($insert_stmt->error));
            exit();
        }
    }
}
?>