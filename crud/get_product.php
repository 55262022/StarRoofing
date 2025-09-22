<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'product' => $row
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
