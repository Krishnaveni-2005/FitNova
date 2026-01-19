<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Check if trainer is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'trainer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$trainer_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$request_id = isset($input['request_id']) ? intval($input['request_id']) : 0;
$action = isset($input['action']) ? $input['action'] : '';

if ($request_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Check if request exists and is pending
$checkSql = "SELECT user_id FROM session_requests WHERE request_id = ? AND trainer_id = ? AND status = 'pending'";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ii", $request_id, $trainer_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Request not found or already processed']);
    $checkStmt->close();
    exit();
}

$requestData = $checkResult->fetch_assoc();
$client_id = $requestData['user_id'];
$checkStmt->close();

// Update status
$new_status = ($action === 'approve') ? 'approved' : 'rejected';
$updateSql = "UPDATE session_requests SET status = ? WHERE request_id = ?";
$stmt = $conn->prepare($updateSql);
$stmt->bind_param("si", $new_status, $request_id);

if ($stmt->execute()) {
    
    // Add Notification for User
    $notifMsg = ($action === 'approve') 
        ? "Your session request has been accepted! Check your schedule." 
        : "Your session request has been declined.";
        
    $notifSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, 'session_request_update', ?)";
    $nStmt = $conn->prepare($notifSql);
    $nStmt->bind_param("is", $client_id, $notifMsg);
    $nStmt->execute();
    $nStmt->close();
    
    // If approved, add to schedules
    if ($action === 'approve') {
        // Find client name
        $nameSql = "SELECT first_name, last_name FROM users WHERE user_id = ?";
        $nameStmt = $conn->prepare($nameSql);
        $nameStmt->bind_param("i", $client_id);
        $nameStmt->execute();
        $nameRes = $nameStmt->get_result()->fetch_assoc();
        $clientName = $nameRes['first_name'] . ' ' . $nameRes['last_name'];
        $nameStmt->close();

        // Add to trainer_schedules
        // Default to Tomorrow at 10 AM, since no negotiation UI yet
        $sessionDate = date('Y-m-d', strtotime('+1 day'));
        $sessionTime = '10:00:00';
        
        $schedSql = "INSERT INTO trainer_schedules (trainer_id, client_name, session_date, session_time, session_type, status) 
                     VALUES (?, ?, ?, ?, 'Training Session', 'scheduled')";
                     
        $schedStmt = $conn->prepare($schedSql);
        $schedStmt->bind_param("issss", $trainer_id, $clientName, $sessionDate, $sessionTime);
        $schedStmt->execute();
        $schedStmt->close();
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
