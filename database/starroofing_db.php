<?php
// database/starroofing_db.php

$host = 'localhost';
$dbname = 'starroofing_db';
$username = 'root';
$password = ''; 

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper encoding
$conn->set_charset("utf8mb4");

/**
 * Verify user credentials
 */
function verify_credentials($email, $password, $conn) {
    $sql = "SELECT a.id, a.email, a.password, a.role_id, a.account_status, 
                   up.first_name, up.last_name
            FROM accounts a 
            LEFT JOIN user_profiles up ON a.id = up.account_id 
            WHERE a.email = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $email);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Update last login
            $update_sql = "UPDATE accounts SET last_login = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            $stmt->close();
            return $user;
        }
    }
    
    $stmt->close();
    return false;
}

/**
 * Check if email exists
 */
function email_exists($email, $conn) {
    $sql = "SELECT id FROM accounts WHERE email = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    
    return $exists;
}
?>