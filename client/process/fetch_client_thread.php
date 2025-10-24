<?php
session_start();
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
if (!$conversation_id) {
    echo json_encode(['success' => false, 'message' => 'Missing conversation ID']);
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
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Fetch conversation (verify it belongs to this user)
$stmt = $conn->prepare("SELECT * FROM conversations WHERE id = ? AND email = ?");
$stmt->bind_param('is', $conversation_id, $user['email']);
$stmt->execute();
$result = $stmt->get_result();
$conversation = $result->fetch_assoc();
$stmt->close();

if (!$conversation) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found or access denied']);
    exit;
}

// Fetch all messages in the conversation with product context
$stmt = $conn->prepare("
    SELECT 
        r.id,
        r.sender,
        r.message,
        r.sent_at,
        r.is_read,
        r.related_product_id,
        p.name as product_name
    FROM replies r
    LEFT JOIN products p ON r.related_product_id = p.product_id
    WHERE r.conversation_id = ?
    ORDER BY r.sent_at ASC
");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Mark admin messages as read
$stmt = $conn->prepare("
    UPDATE replies 
    SET is_read = 1 
    WHERE conversation_id = ? AND sender = 'admin' AND is_read = 0
");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => true,
    'conversation' => $conversation,
    'messages' => $messages
]);
?>