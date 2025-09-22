<?php
session_start();
require_once __DIR__ . '/../database/starroofing_db.php';
require '../vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $_SESSION['error_message'] = 'Please enter a valid email.';
        $_SESSION['old_email'] = $_POST['email'] ?? '';
        header("Location: forgot_password.php");
        exit();
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = 'Email does not exist.';
        $_SESSION['old_email'] = $email;
        header("Location: forgot_password.php");
        exit();
    }

    // Generate OTP
    $code = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime('+5 minutes'));

    // Delete old reset codes
    $delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $delete->bind_param('s', $email);
    $delete->execute();

    // Insert new reset code
    $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at, used) VALUES (?, ?, ?, 0)");
    $insert->bind_param('sss', $email, $code, $expiry);
    $insert->execute();

    // Send Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USER'];
        $mail->Password   = $_ENV['EMAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'Star Roofing & Construction');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body = "
            <p>Hi,</p>
            <p>You requested a password reset for your account.</p>
            <p>Your reset code is:</p>
            <h2 style='color: #4CAF50;'>$code</h2>
            <p><strong>Note:</strong> This code will expire in 5 minutes.</p>
            <br>
            <p>If you did not request this, please ignore this email.</p>
            <p>Regards,<br>Star Roofing & Construction Admin</p>
        ";
        $mail->AltBody = "Your reset code is $code. It will expire in 5 minutes.";

        $mail->send();

        $_SESSION['reset_email'] = $email;
        header("Location: otp.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Mailer Error: ' . $mail->ErrorInfo;
        $_SESSION['old_email'] = $email;
        header("Location: forgot_password.php");
        exit();
    }
}
?>
