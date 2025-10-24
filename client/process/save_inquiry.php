<?php
session_start();
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json; charset=utf-8');
ob_clean();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname    = trim($_POST['firstname'] ?? '');
    $lastname     = trim($_POST['lastname'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $message      = trim($_POST['message'] ?? '');
    $product_id   = intval($_POST['product_id'] ?? 0);
    
    // Address fields
    $region_code  = trim($_POST['region_code'] ?? '');
    $region_name  = trim($_POST['region_name'] ?? '');
    $province_code= trim($_POST['province_code'] ?? '');
    $province_name= trim($_POST['province_name'] ?? '');
    $city_code    = trim($_POST['city_code'] ?? '');
    $city_name    = trim($_POST['city_name'] ?? '');
    $barangay_code= trim($_POST['barangay_code'] ?? '');
    $barangay_name= trim($_POST['barangay_name'] ?? '');
    $street       = trim($_POST['street'] ?? '');

    if (empty($firstname) || empty($lastname) || empty($email) || empty($phone) ||
        empty($region_code) || empty($province_code) || empty($city_code) || empty($barangay_code) || empty($message)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
        exit();
    }

    try {
        $conn->begin_transaction();

        // Check if user already has a conversation
        $stmt = $conn->prepare("SELECT id, is_accepted FROM conversations WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversation = $result->fetch_assoc();
        $stmt->close();

        $conversation_id = null;

        if ($conversation) {
            // Use existing conversation
            $conversation_id = $conversation['id'];
            
            // Update conversation timestamp
            $stmt = $conn->prepare("UPDATE conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param('i', $conversation_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Create new conversation
            $stmt = $conn->prepare("INSERT INTO conversations (email, is_accepted) VALUES (?, 0)");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $conversation_id = $stmt->insert_id;
            $stmt->close();
        }

        // Insert the inquiry
        $stmt = $conn->prepare("
            INSERT INTO inquiries 
            (firstname, lastname, email, phone, message, product_id, conversation_id,
             region_code, region_name, province_code, province_name, city_code, city_name, barangay_code, barangay_name, street) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Build types string dynamically: 5 strings (personal/msg), 2 integers (product_id, conversation_id), then 9 strings (address fields + street)
        $types = str_repeat('s', 5) . 'ii' . str_repeat('s', 9); // total 16 type specifiers
        $stmt->bind_param(
            $types,
            $firstname, $lastname, $email, $phone, $message, $product_id, $conversation_id,
            $region_code, $region_name, $province_code, $province_name, $city_code, $city_name, $barangay_code, $barangay_name, $street
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $inquiry_id = $stmt->insert_id;
        $stmt->close();

        // Add the inquiry message to the conversation as a reply
        $stmt = $conn->prepare("
            INSERT INTO replies (conversation_id, inquiry_id, related_inquiry_id, related_product_id, sender, message)
            VALUES (?, ?, ?, ?, 'client', ?)
        ");
        $stmt->bind_param('iiiis', $conversation_id, $inquiry_id, $inquiry_id, $product_id, $message);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        http_response_code(200);
        echo json_encode([
            'status' => 'success', 
            'message' => 'Inquiry saved successfully!', 
            'inquiry_id' => $inquiry_id,
            'conversation_id' => $conversation_id
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>