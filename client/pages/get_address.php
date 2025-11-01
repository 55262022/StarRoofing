<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['account_id']) || $_SESSION['role_id'] != 2) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../database/starroofing_db.php';

$userId = $_SESSION['account_id'];

if (isset($_GET['id'])) {
    $addressId = intval($_GET['id']);
    
    $sql = "SELECT * FROM user_addresses WHERE id = ? AND account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $addressId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $address = $result->fetch_assoc();
        echo json_encode($address);
    } else {
        echo json_encode(['error' => 'Address not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request']);
}

$conn->close();
?>