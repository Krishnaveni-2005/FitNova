<?php
date_default_timezone_set('Asia/Kolkata');
require 'db_connect.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['token']) || !isset($data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit();
}

$token = $conn->real_escape_string($data['token']);
$password = $data['password'];

// Verify token
$checkToken = "SELECT email FROM password_resets WHERE token = '$token'";
$result = $conn->query($checkToken);

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired reset link. Please request a new one.']);
    exit();
}

$row = $result->fetch_assoc();
$email = $row['email'];

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
