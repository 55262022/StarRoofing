<?php
session_start();
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$account_id = intval($_SESSION['account_id']);
$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$conversation_id || !$message) {
    echo json_encode(['success' => false, 'message' => 'Missing conversation ID or message']);
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

// Verify conversation belongs to user and is accepted
$stmt = $conn->prepare("SELECT is_accepted FROM conversations WHERE id = ? AND email = ?");
$stmt->bind_param('is', $conversation_id, $user['email']);
$stmt->execute();
$conversation = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$conversation) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found or access denied']);
    exit;
}

if (!$conversation['is_accepted']) {
    echo json_encode(['success' => false, 'message' => 'Conversation not yet accepted by admin']);
    exit;
}

// Get the first inquiry ID for this conversation (for replies table requirement)
$stmt = $conn->prepare("SELECT id FROM inquiries WHERE conversation_id = ? ORDER BY submitted_at ASC LIMIT 1");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$first_inquiry = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$first_inquiry) {
    echo json_encode(['success' => false, 'message' => 'No inquiry found for this conversation']);
    exit;
}

$inquiry_id = $first_inquiry['id'];

// Insert the client's reply
$stmt = $conn->prepare("
    INSERT INTO replies 
    (inquiry_id, conversation_id, sender, message) 
    VALUES (?, ?, 'client', ?)
");
$stmt->bind_param('iis', $inquiry_id, $conversation_id, $message);

if ($stmt->execute()) {
    $stmt->close();
    echo json_encode([
        'success' => true,
        'message' => 'Reply sent successfully'
    ]);
} else {
    $stmt->close();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send reply'
    ]);
}
?>