<?php
require 'config.php';
require 'db_connect.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['email']) || !isset($data['otp'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$email = $conn->real_escape_string($data['email']);
$otp = $conn->real_escape_string($data['otp']);

// Check against password_resets table
$check = "SELECT * FROM password_resets WHERE email = '$email' AND token = '$otp' AND expiry > NOW()";
$result = $conn->query($check);

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'success', 'redirect' => 'reset_password_otp.html?email=' . urlencode($email) . '&otp=' . urlencode($otp)]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired OTP']);
}
?>
