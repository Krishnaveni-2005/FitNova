<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['name']) || !isset($data['total']) || !isset($data['available'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$id = intval($data['id']);
$name = $data['name'];
$total = intval($data['total']);
$available = intval($data['available']);

$sql = "UPDATE gym_equipment SET name = ?, total_units = ?, available_units = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siii", $name, $total, $available, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
