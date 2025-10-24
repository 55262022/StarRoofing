<?php
session_start();
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$conversation_id || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
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

// Verify conversation belongs to user and is accepted
$stmt = $conn->prepare("SELECT id, is_accepted FROM conversations WHERE id = ? AND email = ?");
$stmt->bind_param('is', $conversation_id, $user['email']);
$stmt->execute();
$result = $stmt->get_result();
$conversation = $result->fetch_assoc();
$stmt->close();

if (!$conversation) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found or access denied']);
    exit;
}

if (!$conversation['is_accepted']) {
    echo json_encode(['success' => false, 'message' => 'Conversation not yet accepted by admin']);
    exit;
}

// Insert reply
$stmt = $conn->prepare("
    INSERT INTO replies (conversation_id, inquiry_id, sender, message) 
    VALUES (?, NULL, 'client', ?)
");
$stmt->bind_param('is', $conversation_id, $message);

if ($stmt->execute()) {
    // Update conversation timestamp
    $update = $conn->prepare("UPDATE conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $update->bind_param('i', $conversation_id);
    $update->execute();
    $update->close();
    
    echo json_encode(['success' => true, 'message' => 'Message sent']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $stmt->error]);
}

$stmt->close();
?>