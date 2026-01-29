<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Check Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id']) && isset($data['new_role'])) {
    $userId = intval($data['user_id']);
    $newRole = $conn->real_escape_string($data['new_role']);

    if (!in_array($newRole, ['free', 'pro', 'elite'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
        exit();
    }

    $sql = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newRole, $userId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Subscription updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}
?>
