<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

// Get POST values
$id = isset($_POST['inquiry_id']) ? (int)$_POST['inquiry_id'] : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$id || !$message) {
    echo json_encode(['success' => false, 'message' => 'Missing inquiry ID or message']);
    exit;
}

// Insert the reply
$stmt = $conn->prepare("SELECT conversation_id, product_id FROM inquiries WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$inq = $res->fetch_assoc();
$stmt->close();

$conversation_id = isset($inq['conversation_id']) ? intval($inq['conversation_id']) : 0;
$product_id = isset($inq['product_id']) ? intval($inq['product_id']) : 0;

if (!$conversation_id) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found for this inquiry']);
    exit;
}

// Insert reply with conversation_id and inquiry_id so clients fetching by conversation get it
$stmt = $conn->prepare("INSERT INTO replies (conversation_id, inquiry_id, related_inquiry_id, related_product_id, sender, message) VALUES (?, ?, ?, ?, 'admin', ?)");
$stmt->bind_param('iiiis', $conversation_id, $id, $id, $product_id, $message);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'Failed to save reply']);
    exit;
}

// Fetch the inquiry
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone, message, submitted_at FROM inquiries WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$inquiry = $res->fetch_assoc();
$stmt->close();

if (!$inquiry) {
    echo json_encode(['success' => false, 'message' => 'Inquiry not found']);
    exit;
}

// Fetch replies by conversation so both admin and client see the same thread
$stmt = $conn->prepare("SELECT id, sender, message, sent_at FROM replies WHERE conversation_id = ? ORDER BY sent_at ASC");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$res = $stmt->get_result();
$replies = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Return JSON in a format compatible with your JS
echo json_encode([
    'success' => true,
    'inquiry' => $inquiry,
    'replies' => $replies
]);
?>