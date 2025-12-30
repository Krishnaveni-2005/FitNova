<?php
date_default_timezone_set('Asia/Kolkata');
require 'db_connect.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['email']) || !isset($data['otp']) || !isset($data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit();
}

$email = $conn->real_escape_string($data['email']);
$otp = $conn->real_escape_string($data['otp']);
$password = $data['password'];

// Verify OTP again
$checkToken = "SELECT email FROM password_resets WHERE email = '$email' AND token = '$otp' AND expiry > NOW()";
$result = $conn->query($checkToken);

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired OTP. Please start again.']);
    exit();
}

// Update password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$updateSql = "UPDATE users SET password_hash = '$hashed_password' WHERE email = '$email'";

if ($conn->query($updateSql) === TRUE) {
    // Cleanup token
    $conn->query("DELETE FROM password_resets WHERE email = '$email'");
    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
}

$conn->close();
?>
