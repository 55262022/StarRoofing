<?php
session_start();
include '../database/starroofing_db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => true, 'exists' => true]);
    } else {
        echo json_encode(['success' => true, 'exists' => false]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>