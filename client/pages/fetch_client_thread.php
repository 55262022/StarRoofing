<?php
session_start();
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

if (!isset($_GET['conversation_id'])) {
    echo json_encode(['success' => false, 'message' => 'Conversation ID required']);
    exit;
}

$conversation_id = intval($_GET['conversation_id']);
$account_id = intval($_SESSION['account_id']);

// Verify the conversation belongs to this user
$stmt = $conn->prepare("
    SELECT c.*, a.email 
    FROM conversations c 
    JOIN accounts a ON a.id = ? 
    WHERE c.id = ? AND c.email = a.email
");
$stmt->bind_param('ii', $account_id, $conversation_id);
$stmt->execute();
$conversation = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$conversation) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found']);
    exit;
}

// Get all messages in chronological order
$stmt = $conn->prepare("
    SELECT 
        CASE 
            WHEN r.id IS NOT NULL THEN r.message 
            ELSE i.message 
        END as message,
        CASE 
            WHEN r.id IS NOT NULL THEN r.sender
            ELSE 'client'
        END as sender,
        CASE 
            WHEN r.id IS NOT NULL THEN r.sent_at
            ELSE i.submitted_at
        END as sent_at
    FROM conversations c
    LEFT JOIN inquiries i ON i.conversation_id = c.id
    LEFT JOIN replies r ON r.conversation_id = c.id
    WHERE c.id = ?
    ORDER BY sent_at ASC
");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
    'messages' => $messages,
    'conversation' => $conversation
]);