<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['trainer_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing trainer ID']);
    exit();
}

$updateFields = [];
$types = "";
$params = [];

// Allowed fields to update
$allowed = [
    'phone' => 's', 
    'trainer_specialization' => 's', 
    'experience_years' => 'i', 
    'bio' => 's', 
    'email' => 's',
    'first_name' => 's',
    'last_name' => 's'
];

foreach ($allowed as $field => $type) {
    if (isset($input[$field])) {
        $updateFields[] = "$field = ?";
        $types .= $type;
        $params[] = $input[$field];
    }
}

if (empty($updateFields)) {
    echo json_encode(['status' => 'success', 'message' => 'No changes made']);
    exit();
}

$sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
$types .= 'i';
$params[] = $input['trainer_id'];

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Trainer details updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $conn->error]);
}

$conn->close();
?>
