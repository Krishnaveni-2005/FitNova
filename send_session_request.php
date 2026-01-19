<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$trainer_id = $data['trainer_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if (!$trainer_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid trainer']);
    exit();
}

// Check if already pending logic
$check = $conn->prepare("SELECT request_id FROM session_requests WHERE user_id = ? AND trainer_id = ? AND status = 'pending'");
$check->bind_param("ii", $user_id, $trainer_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Request already pending. Please wait for the trainer to respond.']);
    exit();
}
$check->close();

// Insert Logic
$stmt = $conn->prepare("INSERT INTO session_requests (user_id, trainer_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $user_id, $trainer_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
$stmt->close();
$conn->close();
?>
