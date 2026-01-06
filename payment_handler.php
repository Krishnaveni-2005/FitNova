<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Please log in first"]);
    exit();
}

$userId = $_SESSION['user_id'];
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$plan = $conn->real_escape_string($data['plan'] ?? '');
$billing = $conn->real_escape_string($data['billing'] ?? '');

if (empty($plan)) {
    echo json_encode(["status" => "error", "message" => "Invalid plan selected"]);
    exit();
}

// Map plans to roles
$role = "free";
if ($plan === 'pro') $role = 'pro';
if ($plan === 'elite') $role = 'elite';

// Update the user's role in the database
$updateSql = "UPDATE users SET role = ? WHERE user_id = ?";
$stmt = $conn->prepare($updateSql);
$stmt->bind_param("si", $role, $userId);

if ($stmt->execute()) {
    $_SESSION['user_role'] = $role; // Update session
    echo json_encode(["status" => "success", "message" => "Plan updated successfully", "redirect" => $role . "user_dashboard.php"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
