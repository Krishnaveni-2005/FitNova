<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$clientId = $input['client_id'] ?? 0;
$trainerId = $input['trainer_id'] ?? 0;

if ($clientId && $trainerId) {
    // 1. Update Client Status (Invite Sent)
    $stmt = $conn->prepare("UPDATE users SET assigned_trainer_id = ?, assignment_status = 'trainer_invite' WHERE user_id = ?");
    $stmt->bind_param("ii", $trainerId, $clientId);
    $stmt->execute();
    
    // 2. Update Applications
    $conn->query("UPDATE trainer_applications SET status = 'approved' WHERE client_id = $clientId AND trainer_id = $trainerId");
    $conn->query("UPDATE trainer_applications SET status = 'rejected' WHERE client_id = $clientId AND trainer_id != $trainerId");
    
    // 3. Notifications
    // To Client
    $tRes = $conn->query("SELECT first_name, last_name FROM users WHERE user_id = $trainerId");
    if ($tRow = $tRes->fetch_assoc()) {
        $tName = $tRow['first_name'] . ' ' . $tRow['last_name'];
        $conn->query("INSERT INTO user_notifications (user_id, notification_type, message) VALUES ($clientId, 'trainer_match', 'Match Found! Coach $tName has sent you a training request.')");
    }
    
    // To Trainer
    $cRes = $conn->query("SELECT first_name FROM users WHERE user_id = $clientId");
    if ($cRow = $cRes->fetch_assoc()) {
        $cName = $cRow['first_name'];
        $conn->query("INSERT INTO user_notifications (user_id, notification_type, message) VALUES ($trainerId, 'admin_match_approved', 'Admin approved your application for $cName. Invite sent.')");
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
?>
