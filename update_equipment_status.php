<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['equipment_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$id = intval($data['equipment_id']);
$status = $data['status'];

// Normalize status to match what the dashboard expects somewhat, or just store exact string
// Dashboard displays status badge based on 'status-available', 'status-maintenance'
// The user request shows buttons "Available" and "Maintenance".
// I will store these properly capitalized.

$statusText = 'Available';
if (strtolower($status) === 'maintenance') {
    $statusText = 'Maintenance';
} elseif (strtolower($status) === 'available') {
    $statusText = 'Available';
} else {
    $statusText = $status;
}

// Logic: If setting to Maintenance, maybe set available_units to 0? Or just change status label?
// For now, just change status label.

$sql = "UPDATE gym_equipment SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $statusText, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
