<?php
session_start();
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$message = trim($data['message'] ?? '');
$user_type = $data['user_type'] ?? 'guest';

// Validation
if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message is required']);
    exit;
}

try {
    if ($user_type === 'registered' && isset($_SESSION['account_id'])) {
        // Registered user - get email from session
        $account_id = $_SESSION['account_id'];
        
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
        
        // Get user profile for first/last name
        $stmt = $conn->prepare("SELECT first_name, last_name FROM user_profiles WHERE account_id = ?");
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        $firstname = $profile['first_name'] ?? 'User';
        $lastname = $profile['last_name'] ?? '';
        
    } else {
        // Guest user - get from submitted data
        $firstname = trim($data['first_name'] ?? '');
        $lastname = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        
        if (empty($firstname) || empty($lastname) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            exit;
        }
    }
    
    $conn->begin_transaction();
    
    // Check if conversation already exists for this email
    $stmt = $conn->prepare("SELECT id, is_accepted FROM conversations WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversation = $result->fetch_assoc();
    $stmt->close();
    
    $conversation_id = null;
    
    if ($conversation) {
        // Conversation exists - update timestamp
        $conversation_id = $conversation['id'];
        
        $stmt = $conn->prepare("UPDATE conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param('i', $conversation_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Create new conversation
        $stmt = $conn->prepare("INSERT INTO conversations (email, is_accepted) VALUES (?, 0)");
        $stmt->bind_param('s', $email);
        
        if ($stmt->execute()) {
            $conversation_id = $stmt->insert_id;
        }
        $stmt->close();
    }
    
    if (!$conversation_id) {
        throw new Exception('Failed to create conversation');
    }
    
    // Create inquiry record with source='chatbot'
    $stmt = $conn->prepare("
        INSERT INTO inquiries 
        (firstname, lastname, email, phone, region_code, region_name, 
         province_code, province_name, city_code, city_name, 
         barangay_code, barangay_name, street, message, 
         conversation_id, is_accepted, source) 
        VALUES (?, ?, ?, 'N/A', '', '', '', '', '', '', '', '', '', ?, ?, 0, 'chatbot')
    ");
    
    $stmt->bind_param('ssssi', 
        $firstname, 
        $lastname, 
        $email, 
        $message,
        $conversation_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save inquiry');
    }
    
    $inquiry_id = $stmt->insert_id;
    $stmt->close();
    
    // If conversation is already accepted, also add to replies table
    if ($conversation && $conversation['is_accepted']) {
        $stmt = $conn->prepare("
            INSERT INTO replies 
            (conversation_id, inquiry_id, related_inquiry_id, sender, message)
            VALUES (?, ?, ?, 'client', ?)
        ");
        $stmt->bind_param('iiis', $conversation_id, $inquiry_id, $inquiry_id, $message);
        $stmt->execute();
        $stmt->close();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Message sent successfully',
        'conversation_id' => $conversation_id,
        'inquiry_id' => $inquiry_id,
        'is_accepted' => $conversation ? (bool)$conversation['is_accepted'] : false
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Chatbot save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}

$conn->close();
?>