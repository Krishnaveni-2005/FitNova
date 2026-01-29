<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

// Check Admin Auth or Gym Owner Auth
$allowed_gym_owner = 'ashakayaplackal@gmail.com';
if (
    !isset($_SESSION['user_id']) || 
    !isset($_SESSION['user_role']) || 
    ($_SESSION['user_role'] !== 'admin' && strtolower($_SESSION['user_email']) !== $allowed_gym_owner)
) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// Read JSON Input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit();
}

$action = $data['action'] ?? '';
$trainer_id = $data['trainer_id'] ?? 0;

if (empty($action) || empty($trainer_id)) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit();
}

$status = '';
if ($action === 'approve') {
    $status = 'active';
} elseif ($action === 'reject') {
    $status = 'inactive'; // Or 'rejected' if ENUM supports it, sticking to inactive for now or delete
    // For now, let's just use 'inactive' as rejection basically
    // If you want proper rejection tracking, add 'rejected' to ENUM
    // Checking previous steps, I added 'suspended' and 'pending'. 'inactive' is fine.
} elseif ($action === 'delete') {
    // Optional: hard delete
    $delStmt = $conn->prepare("UPDATE users SET account_status = 'deleted' WHERE user_id = ? AND role = 'trainer'");
    $delStmt->bind_param("i", $trainer_id);
    if ($delStmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Trainer deleted"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting trainer"]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
    exit();
}

// Update Status
$stmt = $conn->prepare("UPDATE users SET account_status = ? WHERE user_id = ? AND role = 'trainer'");
$stmt->bind_param("si", $status, $trainer_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Trainer request " . $action . "d"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error"]);
}

$stmt->close();
$conn->close();
?>

