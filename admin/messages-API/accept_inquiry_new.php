<?php
// Ensure no errors are output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';

// Clear any output that might have been generated
ob_clean();
header('Content-Type: application/json');

function sendJsonResponse($success, $message, $data = []) {
    echo json_encode(array_merge(
        ['success' => $success, 'message' => $message],
        $data
    ));
    exit;
}

// Get inquiry ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
    sendJsonResponse(false, 'Missing inquiry ID');
}

// Start transaction
if (!mysqli_begin_transaction($conn)) {
    sendJsonResponse(false, 'Could not start transaction');
}

// Get inquiry details first
$stmt = $conn->prepare("SELECT conversation_id, email FROM inquiries WHERE id = ? LIMIT 1");
if (!$stmt) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Database error: ' . $conn->error);
}

$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Could not fetch inquiry details');
}

$res = $stmt->get_result();
$inquiry = $res->fetch_assoc();
$stmt->close();

if (!$inquiry) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Inquiry not found');
}

$conversation_id = $inquiry['conversation_id'];

// Mark the inquiry as accepted
$stmt = $conn->prepare("UPDATE inquiries SET is_accepted = 1 WHERE id = ?");
if (!$stmt) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Database error updating inquiry');
}

$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Failed to update inquiry');
}
$stmt->close();

// Also mark all other inquiries from same conversation as accepted
$stmt = $conn->prepare("
    UPDATE inquiries 
    SET is_accepted = 1 
    WHERE conversation_id = ?
");
if (!$stmt) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Database error updating related inquiries');
}

$stmt->bind_param('i', $conversation_id);
if (!$stmt->execute()) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Failed to update related inquiries');
}
$stmt->close();

// Mark the conversation as accepted
$stmt = $conn->prepare("
    UPDATE conversations 
    SET is_accepted = 1,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = ?
");
if (!$stmt) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Database error updating conversation');
}

$stmt->bind_param('i', $conversation_id);
if (!$stmt->execute()) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Failed to update conversation');
}
$stmt->close();

// Commit transaction
if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    sendJsonResponse(false, 'Failed to commit changes');
}

// Log the acceptance for debugging
error_log("Inquiry #$id accepted. Conversation #$conversation_id updated.");

sendJsonResponse(true, 'Inquiry accepted successfully', [
    'conversation_id' => $conversation_id
]);
?>