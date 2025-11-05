<?php
// Increase PHP limits for large uploads
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('memory_limit', '256M');

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

    // Handle image upload with 100MB limit
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file_size = $_FILES['image_file']['size'];
        $max_size = 100 * 1024 * 1024; // 100MB in bytes
        
        // Validate file size
        if ($file_size > $max_size) {
            header("Location: ../admin/inventory.php?error=Image size must be less than 100MB");
            exit();
        }
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image_file']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            header("Location: ../admin/inventory.php?error=Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.");
            exit();
        }
        
        $upload_dir = "../uploads/products/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
            $image_path = "uploads/products/" . $filename;
        } else {
            header("Location: ../admin/inventory.php?error=Failed to upload image");
            exit();
        }
    } else if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors
        $error_message = "Upload failed: ";
        switch ($_FILES['image_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message .= "File is too large (max 100MB)";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message .= "File was only partially uploaded";
                break;
            default:
                $error_message .= "Unknown error occurred";
        }
        header("Location: ../admin/inventory.php?error=" . urlencode($error_message));
        exit();
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