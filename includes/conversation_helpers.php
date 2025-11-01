<?php
function get_or_create_conversation($conn, $email) {
    // Check if conversation exists
    $stmt = $conn->prepare("SELECT id, is_accepted FROM conversations WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversation = $result->fetch_assoc();
    $stmt->close();

    if ($conversation) {
        // Update last activity time
        $stmt = $conn->prepare("UPDATE conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param('i', $conversation['id']);
        $stmt->execute();
        $stmt->close();
        return $conversation;
    }

    // Create new conversation if none exists
    $stmt = $conn->prepare("INSERT INTO conversations (email, is_accepted) VALUES (?, 0)");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $conversation_id = $stmt->insert_id;
    $stmt->close();

    return ['id' => $conversation_id, 'is_accepted' => 0];
}
?>