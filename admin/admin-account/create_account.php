<?php
session_start();
include '../database/starroofing_db.php';
require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email.']);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Email is already registered.']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Use default password from .env
    $default_password = $_ENV['DEFAULT_PASSWORD'] ?? null;
    if (!$default_password) {
        echo json_encode(['success' => false, 'error' => 'Default password is not configured.']);
        exit;
    }

    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

    // Insert into database with default values
    $stmt = $conn->prepare("INSERT INTO accounts (email, password, first_name, last_name, role_id, account_status) VALUES (?, ?, ?, ?, ?, ?)");
    
    $first_name = "Admin";
    $last_name = "User";
    $role_id = 2; // Admin role
    $account_status = "active";
    
    $stmt->bind_param("ssssis", $email, $hashed_password, $first_name, $last_name, $role_id, $account_status);

    if ($stmt->execute()) {
        // Send welcome email
        $email_sent = sendWelcomeEmail($email, $default_password);
        
        if ($email_sent) {
            echo json_encode(['success' => true, 'message' => 'Account created successfully. Email sent with credentials.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Account created successfully but email could not be sent.']);
        }
    } else {
        error_log("Database error: " . $stmt->error);
        echo json_encode(['success' => false, 'error' => 'Database error while adding admin: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

if (isset($conn)) {
    $conn->close();
}

function sendWelcomeEmail($email, $password) {
    try {
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'Star Roofing & Construction');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Admin Account Created - Star Roofing & Construction';
        $mail->Body = "
            <h2>Admin Account Created</h2>
            <p>You have been added as an admin to Star Roofing & Construction.</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Temporary Password:</strong> $password</p>
            <p>Please change your password after logging in for security purposes.</p>
            <br>
            <p>Best regards,<br>Star Roofing & Construction Developer</p>
        ";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return false;
    }
}
