<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'trainer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$clientId = $input['client_id'] ?? 0;
$trainerId = $_SESSION['user_id'];

if ($clientId > 0) {
    // Verify that this was suggested by Admin
    $chkSql = "SELECT status FROM trainer_applications WHERE client_id = ? AND trainer_id = ? AND status = 'admin_suggested'";
    $stmt = $conn->prepare($chkSql);
    $stmt->bind_param("ii", $clientId, $trainerId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $stmt->close();
        
        // Accept the suggestion and Send Invite
        // 1. Update Application Status
        $upApp = $conn->prepare("UPDATE trainer_applications SET status = 'approved' WHERE client_id = ? AND trainer_id = ?");
        $upApp->bind_param("ii", $clientId, $trainerId);
        $upApp->execute();
        
        // 2. Update Client Status (Invite Sent)
        $upUser = $conn->prepare("UPDATE users SET assigned_trainer_id = ?, assignment_status = 'trainer_invite' WHERE user_id = ?");
        $upUser->bind_param("ii", $trainerId, $clientId);
        $upUser->execute();
        
        // 3. Notify Client
        $trainerName = $_SESSION['user_name'] ?? 'Your Trainer';
        $msg = "Coach $trainerName has sent you a training invite based on Admin recommendation.";
        $conn->query("INSERT INTO user_notifications (user_id, notification_type, message) VALUES ($clientId, 'trainer_invite', '$msg')");
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Opportunity is no longer available or valid.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
?>
