<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

// Get inquiry ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing inquiry ID']);
    exit;
}

// Begin transaction to update inquiry and conversation together
$conn->begin_transaction();
try {
    // Mark the inquiry accepted
    $stmt = $conn->prepare("UPDATE inquiries SET is_accepted = 1 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $ok1 = $stmt->execute();
    $stmt->close();

    if (!$ok1) {
        throw new Exception('Failed to update inquiry');
    }

    // Find the conversation_id for this inquiry
    $stmt = $conn->prepare("SELECT conversation_id FROM inquiries WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    $conversation_id = $row['conversation_id'] ?? null;

    if ($conversation_id) {
        // Mark conversation as accepted as well
        $stmt = $conn->prepare("UPDATE conversations SET is_accepted = 1 WHERE id = ?");
        $stmt->bind_param('i', $conversation_id);
        $ok2 = $stmt->execute();
        $stmt->close();

        if (!$ok2) {
            throw new Exception('Failed to update conversation');
        }
    }

    $conn->commit();

    echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>