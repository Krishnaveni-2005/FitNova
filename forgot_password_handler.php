<?php
date_default_timezone_set('Asia/Kolkata');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'config.php';
require 'db_connect.php';
require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/Exception.php';
require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/SMTP.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit();
}

$email = $conn->real_escape_string($data['email']);

// Check if user exists
$checkUser = "SELECT * FROM users WHERE email = '$email'";
$userResult = $conn->query($checkUser);

if ($userResult->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found in database for email: ' . $email]);
    exit();
}

// Generate OTP
$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime('+15 minutes')); // OTPs should have shorter expiry

// Create resets table if not exists (using existing structure)
$conn->query("CREATE TABLE IF NOT EXISTS password_resets (
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expiry DATETIME NOT NULL,
    PRIMARY KEY (email)
)");

// Store OTP
$conn->query("DELETE FROM password_resets WHERE email = '$email'"); // Remove old OTPs
$conn->query("INSERT INTO password_resets (email, token, expiry) VALUES ('$email', '$otp', '$expiry')");

// Send Email
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;

    // Recipients
    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Password Reset OTP - FitNova';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
            <h2 style='color: #0F2C59;'>FitNova Password Reset</h2>
            <p>Use the following One-Time Password (OTP) to verify your identity and reset your password:</p>
            <div style='text-align: center; margin: 30px 0;'>
                <span style='background-color: #f0f0f0; color: #0F2C59; padding: 15px 30px; font-size: 24px; font-weight: bold; letter-spacing: 5px; border-radius: 5px; border: 1px solid #ddd;'>$otp</span>
            </div>
            <p>This OTP is valid for 15 minutes.</p>
            <p style='color: #666; font-size: 0.8rem;'>If you didn't request this, you can safely ignore this email.</p>
        </div>
    ";

    file_put_contents('mail_log.txt', "Attempting to send OTP to $email at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    $mail->send();
    file_put_contents('mail_log.txt', "Success sending OTP to $email\n", FILE_APPEND);
    echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully', 'redirect' => 'verify_otp.php?email=' . urlencode($email)]);
} catch (Exception $e) {
    file_put_contents('mail_log.txt', "FAILED sending to $email: " . $mail->ErrorInfo . "\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => "SMTP Error: " . $mail->ErrorInfo]);
}
?>
