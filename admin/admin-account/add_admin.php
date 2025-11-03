<?php
session_start();
include '../database/starroofing_db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Email is already registered.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    $default_password = $ENV['DEFAULT_PASSWORD'];
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO accounts (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashed_password);

    if ($stmt->execute()) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['EMAIL_USER'];
            $mail->Password = $_ENV['EMAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['EMAIL_USER'], 'Star Roofing & Construction Developer');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Admin Account Created';
            $mail->Body = "
              <p>You have been added as an admin.</p>
              <p><strong>Email:</strong> $email</p>
              <p><strong>Temporary Password:</strong> $default_password</p>
              <p>Please change your password for security purpose.</p>
            ";

            $mail->send();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Email sending failed: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error while adding admin.']);
    }

    $stmt->close();
    $conn->close();
}
?>
