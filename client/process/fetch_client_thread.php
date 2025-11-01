<?php
session_start();
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$account_id = intval($_SESSION['account_id']);
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

if (!$conversation_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation ID']);
    exit;
}

// Get user email
$stmt = $conn->prepare("SELECT email FROM accounts WHERE id = ?");
$stmt->bind_param('i', $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Verify conversation belongs to user
$stmt = $conn->prepare("SELECT is_accepted FROM conversations WHERE id = ? AND email = ?");
$stmt->bind_param('is', $conversation_id, $user['email']);
$stmt->execute();
$conversation = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$conversation) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found or access denied']);
    exit;
}

// Fetch all messages (initial inquiries + replies)
$messages = [];

// Get all inquiries for this conversation
$stmt = $conn->prepare("
    SELECT 
        i.id,
        i.message,
        i.submitted_at as sent_at,
        'client' as sender,
        p.name as product_name
    FROM inquiries i
    LEFT JOIN products p ON i.product_id = p.product_id
    WHERE i.conversation_id = ?
    ORDER BY i.submitted_at ASC
");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

// Get all replies for this conversation
$stmt = $conn->prepare("
    SELECT 
        r.id,
        r.message,
        r.sent_at,
        r.sender,
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
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

// Sort all messages by timestamp
usort($messages, function($a, $b) {
    return strtotime($a['sent_at']) - strtotime($b['sent_at']);
});

// Mark admin replies as read
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
    'messages' => $messages,
    'is_accepted' => (bool)$conversation['is_accepted']
]);
?>