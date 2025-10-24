<?php
session_start();
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

$account_id = intval($_SESSION['account_id']);

// Get user email
$stmt = $conn->prepare("SELECT email FROM accounts WHERE id = ?");
$stmt->bind_param('i', $account_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

// Count unread admin replies for this user's inquiries
$stmt = $conn->prepare("
    SELECT COUNT(*) as unread_count
    FROM replies r
    INNER JOIN inquiries i ON r.inquiry_id = i.id
    WHERE i.email = ? 
    AND r.sender = 'admin' 
    AND r.is_read = 0
");
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

echo json_encode([
    'success' => true,
    'count' => intval($data['unread_count'])
]);
?>