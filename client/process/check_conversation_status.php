<?php
session_start();
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

// This endpoint checks if user's conversation has been accepted
// Call this periodically from the chatbot or client-messages page

if (!isset($_SESSION['account_id'])) {
    // For guest users, check by email stored in localStorage
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
    
    // Check conversation status for guest
    $stmt = $conn->prepare("
        SELECT c.id, c.is_accepted, c.updated_at,
               COUNT(r.id) as new_replies
        FROM conversations c
        LEFT JOIN replies r ON r.conversation_id = c.id 
            AND r.sender = 'admin' 
            AND r.is_read = 0
        WHERE c.email = ?
        GROUP BY c.id
        LIMIT 1
    ");
    $stmt->bind_param('s', $email);
} else {
    // For logged-in users, use session
    $account_id = intval($_SESSION['account_id']);
    
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
    
    // Check conversation status
    $stmt = $conn->prepare("
        SELECT c.id, c.is_accepted, c.updated_at,
               COUNT(r.id) as new_replies
        FROM conversations c
        LEFT JOIN replies r ON r.conversation_id = c.id 
            AND r.sender = 'admin' 
            AND r.is_read = 0
        WHERE c.email = ?
        GROUP BY c.id
        LIMIT 1
    ");
    $stmt->bind_param('s', $email);
}

$stmt->execute();
$result = $stmt->get_result();
$conversation = $result->fetch_assoc();
$stmt->close();

if (!$conversation) {
    echo json_encode([
        'success' => true,
        'has_conversation' => false,
        'is_accepted' => false,
        'new_replies' => 0
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'has_conversation' => true,
    'conversation_id' => intval($conversation['id']),
    'is_accepted' => (bool)$conversation['is_accepted'],
    'new_replies' => intval($conversation['new_replies']),
    'last_update' => $conversation['updated_at']
]);
?>