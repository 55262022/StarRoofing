<?php
// Increase PHP limits for large uploads
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('memory_limit', '256M');

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

    // Handle file upload if a new image is provided with 100MB limit
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
            
            // Optional: Delete old image if exists
            $old_image_sql = "SELECT image_path FROM products WHERE product_id = ?";
            $old_stmt = $conn->prepare($old_image_sql);
            $old_stmt->bind_param("i", $product_id);
            $old_stmt->execute();
            $old_result = $old_stmt->get_result();
            
            if ($old_result && $old_row = $old_result->fetch_assoc()) {
                $old_image = $old_row['image_path'];
                if ($old_image && file_exists("../" . $old_image)) {
                    unlink("../" . $old_image);
                }
            }
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