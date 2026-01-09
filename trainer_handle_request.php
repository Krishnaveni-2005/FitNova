<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'trainer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $clientId = $data['client_id'] ?? null;
    $action = $data['action'] ?? null;

    if (!$clientId || !$action) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit();
    }

    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    // If rejected, we might want to unassign (set assigned_trainer_id to NULL) or keep as rejected record?
    // User said "only allow clients who got approval". 'rejected' status works. 
    // Or if rejected, maybe set status to 'none' and trainer_id to NULL?
    // Let's set to 'rejected' for now so they know.

    $sql = "UPDATE users SET assignment_status = ? WHERE user_id = ? AND assigned_trainer_id = ?";
    $stmt = $conn->prepare($sql);
    $trainerId = $_SESSION['user_id'];
    $stmt->bind_param("sii", $status, $clientId, $trainerId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>
