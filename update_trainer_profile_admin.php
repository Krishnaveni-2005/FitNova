<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['first_name']) || !isset($data['last_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$id = intval($data['user_id']);
$firstName = $data['first_name'];
$lastName = $data['last_name'];
$specialization = $data['specialization'] ?? '';
$experience = floatval($data['experience'] ?? 0);
$bio = $data['bio'] ?? '';

$sql = "UPDATE users SET 
        first_name = ?, 
        last_name = ?, 
        trainer_specialization = ?, 
        trainer_experience = ?, 
        bio = ? 
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssdsi", $firstName, $lastName, $specialization, $experience, $bio, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
