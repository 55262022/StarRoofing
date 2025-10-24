<?php
include '../../database/starroofing_db.php';

// Check if inquiry_id is provided
if (!isset($_GET['inquiry_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing inquiry ID']);
    exit;
}

$inquiry_id = intval($_GET['inquiry_id']);

try {
    // Fetch the client’s inquiry details
    $stmt_inquiry = $conn->prepare("
        SELECT i.id, i.firstname, i.lastname, i.email, i.phone, i.message, i.submitted_at, 
               p.name AS product_name
        FROM inquiries i
        LEFT JOIN products p ON i.product_id = p.product_id
        WHERE i.id = ?
    ");
    $stmt_inquiry->execute([$inquiry_id]);
    $inquiry = $stmt_inquiry->fetch(PDO::FETCH_ASSOC);

    if (!$inquiry) {
        echo json_encode(['status' => 'error', 'message' => 'Inquiry not found']);
        exit;
    }

    // Fetch all replies for that inquiry (admin and client)
    $stmt_replies = $conn->prepare("
        SELECT id, sender, message, sent_at, is_read
        FROM replies
        WHERE inquiry_id = ?
        ORDER BY sent_at ASC
    ");
    $stmt_replies->execute([$inquiry_id]);
    $replies = $stmt_replies->fetchAll(PDO::FETCH_ASSOC);

    // Combine results
    $response = [
        'status' => 'success',
        'inquiry' => $inquiry,
        'conversation' => $replies
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>