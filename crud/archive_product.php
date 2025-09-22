<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

if (isset($_POST['archive_product'])) {
    $product_id = intval($_POST['product_id']);

    $stmt = $conn->prepare("UPDATE products SET is_archived = 1 WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        header("Location: ../admin/inventory.php?success=Product archived successfully");
    } else {
        header("Location: ../admin/inventory.php?success=Failed to archive product");
    }
    exit();
}
?>
