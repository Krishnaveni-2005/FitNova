<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$trainer_id = $data['trainer_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if (!$trainer_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid trainer']);
    exit();
}

// Check if already pending logic
$check = $conn->prepare("SELECT request_id FROM session_requests WHERE user_id = ? AND trainer_id = ? AND status = 'pending'");
$check->bind_param("ii", $user_id, $trainer_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Request already pending. Please wait for the trainer to respond.']);
    exit();
}
$check->close();

// Insert Logic
$stmt = $conn->prepare("INSERT INTO session_requests (user_id, trainer_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $user_id, $trainer_id);

$requestSuccess = false;

if ($stmt->execute()) {
    $requestSuccess = true;
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
$stmt->close();

if ($requestSuccess) {
    // Notify Admin (WhatsApp + Dashboard)
    require_once 'admin_notifications.php';
    
    // Fetch names for the message
    $cRes = $conn->query("SELECT first_name, last_name FROM users WHERE user_id = $user_id");
    $clientName = "Client";
    if ($cRow = $cRes->fetch_assoc()) {
        $clientName = $cRow['first_name'] . ' ' . $cRow['last_name'];
    }

    $tRes = $conn->query("SELECT first_name, last_name FROM users WHERE user_id = $trainer_id");
    $trainerName = "Trainer";
    if ($tRow = $tRes->fetch_assoc()) {
        $trainerName = $tRow['first_name'] . ' ' . $tRow['last_name'];
    }

    $adminMsg = "New Session Request: Client $clientName has requested a session with Coach $trainerName.";
    
    if (function_exists('sendAdminNotification')) {
        sendAdminNotification($conn, $adminMsg);
    }

    echo json_encode(['success' => true]);
}
$conn->close();
?>
