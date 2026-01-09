<?php
session_start();
require 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$trainerId = isset($_POST['trainer_id']) ? intval($_POST['trainer_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($trainerId <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Update trainer status based on action
if ($action === 'approve') {
    $newStatus = 'active';
    $message = 'Trainer approved successfully';
} else {
    $newStatus = 'inactive';
    $message = 'Trainer rejected';
}

$sql = "UPDATE users SET account_status = ? WHERE id = ? AND role = 'trainer'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newStatus, $trainerId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
