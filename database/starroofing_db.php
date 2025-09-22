<?php
// database/starroofing_db.php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env from project root
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host     = $_ENV['DB_HOST'];
$dbname   = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
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
        
        if (password_verify($password, $user['password'])) {
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
