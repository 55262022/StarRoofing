<?php
session_start();
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Get JSON POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['conversation_id']) || !isset($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$conversation_id = intval($data['conversation_id']);
$message = trim($data['message']);
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

// Add the reply
$stmt = $conn->prepare("
    INSERT INTO replies (conversation_id, message, sender, sent_at) 
    VALUES (?, ?, 'client', NOW())
");
$stmt->bind_param('is', $conversation_id, $message);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    // Update conversation timestamp
    $stmt = $conn->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('i', $conversation_id);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}