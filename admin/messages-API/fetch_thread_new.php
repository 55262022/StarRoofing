<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing inquiry ID'
    ]);
    exit;
}

// First get the conversation_id for this inquiry
$stmt = $conn->prepare("SELECT conversation_id FROM inquiries WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$inquiry_data = $res->fetch_assoc();
$stmt->close();

if (!$inquiry_data || !$inquiry_data['conversation_id']) {
    echo json_encode([
        'success' => false,
        'message' => 'Inquiry or conversation not found'
    ]);
    exit;
}

$conversation_id = $inquiry_data['conversation_id'];

// fetch the initial inquiry (for contact details)
$stmt = $conn->prepare("
    SELECT i.id, i.firstname, i.lastname, i.email, i.phone, i.message, i.submitted_at,
           i.region_name, i.province_name, i.city_name, i.barangay_name, i.street,
           c.is_accepted
    FROM inquiries i
    JOIN conversations c ON i.conversation_id = c.id
    WHERE i.conversation_id = ?
    ORDER BY i.submitted_at ASC
    LIMIT 1
");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$res = $stmt->get_result();
$inq = $res->fetch_assoc();
$stmt->close();

if (!$inq) {
    echo json_encode([
        'success' => false,
        'message' => 'Inquiry details not found'
    ]);
    exit;
}

// fetch all replies in this conversation
$stmt = $conn->prepare("
    SELECT r.id, r.sender, r.message, r.sent_at, r.is_read,
           i.product_id as related_product_id 
    FROM replies r
    LEFT JOIN inquiries i ON r.inquiry_id = i.id
    WHERE r.conversation_id = ?
    ORDER BY r.sent_at ASC
");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$res = $stmt->get_result();
$replies = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Mark client messages as read since admin is viewing them
$stmt = $conn->prepare("
    UPDATE replies 
    SET is_read = 1 
    WHERE conversation_id = ? 
    AND sender = 'client'
");
$stmt->bind_param('i', $conversation_id);
$stmt->execute();
$stmt->close();

// return consistent JSON structure with conversation status
echo json_encode([
    'success' => true,
    'inquiry' => $inq,
    'replies' => $replies,
    'is_accepted' => (bool)$inq['is_accepted']
]);
?>