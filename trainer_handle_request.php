<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Check if trainer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'trainer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$trainer_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$client_id = isset($input['client_id']) ? intval($input['client_id']) : 0;
$action = isset($input['action']) ? $input['action'] : '';

if ($client_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Update status
$new_status = ($action === 'approve') ? 'approved' : 'rejected';
// If rejected, maybe we should set assigned_trainer_id to NULL? 
// User request said: "only after the trainer approved that request,the trainer and that client communicate"
// If rejected, they can try again or try another trainer, so setting assigned_trainer_id to NULL makes sense.
// BUT, usually rejection history is good. However, fitnova schema seems simple. Let's keep it 'rejected' or reset.
// Let's reset for rejection so they can hire someone else.
$sql = "";
if ($action === 'approve') {
    $sql = "UPDATE users SET assignment_status = 'approved' WHERE user_id = ? AND assigned_trainer_id = ?";
} else {
    // Reject: Free up the user to hire someone else
    $sql = "UPDATE users SET assignment_status = 'rejected', assigned_trainer_id = NULL WHERE user_id = ? AND assigned_trainer_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $client_id, $trainer_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        
        // Add Notification for User
        $notifMsg = ($action === 'approve') 
            ? "Great news! Your request to hire Coach has been approved." 
            : "Your request to hire Coach was declined.";
            
        $notifSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, 'trainer_request_update', ?)";
        $nStmt = $conn->prepare($notifSql);
        $nStmt->bind_param("is", $client_id, $notifMsg);
        $nStmt->execute();
        $nStmt->close();
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No matching pending request found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
