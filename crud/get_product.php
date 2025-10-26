<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

header('Content-Type: application/json');
requireAdmin();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No product ID']);
    exit();
}

$product_id = intval($_GET['id']);

$sql = "SELECT * FROM products WHERE product_id = ? AND is_archived = 0";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => true,
        'product' => $result->fetch_assoc()
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
}
?>