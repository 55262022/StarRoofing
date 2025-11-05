<?php
require_once '../../database/starroofing_db.php';

// Get webhook payload
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

// Verify signature
// ... implement signature verification

$event = json_decode($payload, true);

if ($event['data']['attributes']['type'] === 'payment.paid') {
    $payment_intent_id = $event['data']['attributes']['data']['id'];
    
    // Update order status
    $query = "UPDATE orders SET payment_status = 'paid' WHERE payment_intent_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $payment_intent_id);
    $stmt->execute();
}

http_response_code(200);
?>