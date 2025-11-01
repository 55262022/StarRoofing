<?php
session_start();
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json');

// Check if user is logged in
$isLoggedIn = isset($_SESSION['account_id']);
$email = null;

if ($isLoggedIn) {
    $account_id = intval($_SESSION['account_id']);
    
    // Get user email
    $stmt = $conn->prepare("SELECT email FROM accounts WHERE id = ?");
    $stmt->bind_param('i', $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    $email = $user['email'];
} else {
    // For guest users, check if email is provided
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $email = isset($data['email']) ? trim($data['email']) : null;
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email required', 'has_conversation' => false]);
        exit;
    }
}

// Get conversation for this email
$stmt = $conn->prepare("SELECT id, is_accepted, created_at FROM conversations WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$conversation = $result->fetch_assoc();
$stmt->close();

if (!$conversation) {
    echo json_encode([
        'success' => true,
        'has_conversation' => false,
        'messages' => []
    ]);
    exit;
}

$conversation_id = $conversation['id'];

// ONLY fetch replies (conversation messages) - NOT the initial inquiry
// This will show only the back-and-forth chat between admin and client
$messages = [];

$stmt = $conn->prepare("
    SELECT 
        r.message,
        r.sender,
        r.sent_at,
        p.name as product_name
    FROM replies r
    LEFT JOIN products p ON r.related_product_id = p.product_id
    WHERE r.conversation_id = ?
    ORDER BY r.sent_at ASC
");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

// Mark admin messages as read for logged-in users
if ($isLoggedIn) {
    $stmt = $conn->prepare("
        UPDATE replies 
        SET is_read = 1 
        WHERE conversation_id = ? AND sender = 'admin' AND is_read = 0
    ");
    $stmt->bind_param('i', $conversation_id);
    $stmt->execute();
    $stmt->close();
}

// Count unread admin messages
$stmt = $conn->prepare("
    SELECT COUNT(*) as unread_count
    FROM replies
    WHERE conversation_id = ? AND sender = 'admin' AND is_read = 0
");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
$unread_data = $result->fetch_assoc();
$stmt->close();

echo json_encode([
    'success' => true,
    'has_conversation' => true,
    'is_accepted' => (bool)$conversation['is_accepted'],
    'conversation_id' => $conversation_id,
    'messages' => $messages,
    'unread_count' => intval($unread_data['unread_count'])
]);
?>