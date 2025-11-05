<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
require_once '../../payment/paymongo-helper.php';
requireAuth();

// Get payment intent ID from URL
$payment_intent_id = $_GET['payment_intent_id'] ?? $_GET['source_id'] ?? null;
$payment_status = $_GET['status'] ?? null;

// Get order number from session
$order_number = $_SESSION['pending_order_number'] ?? null;

if (!$order_number) {
    header('Location: ../materials.php');
    exit();
}

try {
    $paymongo = new PayMongoHelper();
    
    // Verify payment with PayMongo
    if ($payment_intent_id) {
        $paymentDetails = $paymongo->getPaymentIntent($payment_intent_id);
        $paymentStatus = $paymentDetails['data']['attributes']['status'] ?? 'failed';
        
        // Update order payment status in database
        $conn->begin_transaction();
        
        if ($paymentStatus === 'succeeded' || $paymentStatus === 'processing') {
            // Update payment status to paid
            $update_query = "UPDATE orders SET payment_status = 'paid' WHERE order_number = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("s", $order_number);
            $stmt->execute();
            $stmt->close();
            
            // Add to order history
            $order_id_query = "SELECT order_id FROM orders WHERE order_number = ? LIMIT 1";
            $stmt = $conn->prepare($order_id_query);
            $stmt->bind_param("s", $order_number);
            $stmt->execute();
            $order_id = $stmt->get_result()->fetch_assoc()['order_id'];
            $stmt->close();
            
            $history_stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'pending', 'Payment completed successfully')");
            $history_stmt->bind_param("i", $order_id);
            $history_stmt->execute();
            $history_stmt->close();
            
            $conn->commit();
            
            // Clear pending order from session
            unset($_SESSION['pending_order_number']);
            
            // Redirect to success page
            header("Location: order-success.php?order_number=" . urlencode($order_number));
            exit();
            
        } else {
            // Payment failed
            $update_query = "UPDATE orders SET payment_status = 'failed' WHERE order_number = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("s", $order_number);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            
            // Redirect to failed page
            header("Location: payment-failed.php?order_number=" . urlencode($order_number));
            exit();
        }
    } else {
        throw new Exception("Payment verification failed.");
    }
    
} catch (Exception $e) {
    error_log("Payment return error: " . $e->getMessage());
    header("Location: payment-failed.php?order_number=" . urlencode($order_number));
    exit();
}
?>