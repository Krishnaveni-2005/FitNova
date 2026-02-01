<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Check admin authentication or Gym Owner
$allowed_gym_owner = 'ashakayaplackal@gmail.com';
if (
    (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') && 
    (!isset($_SESSION['user_email']) || strtolower($_SESSION['user_email']) !== $allowed_gym_owner)
) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Parse Request
$data = null;
$rawInput = file_get_contents("php://input");
if (!empty($rawInput)) {
    $data = json_decode($rawInput, true);
}

// Fallback to $_POST if JSON failed or was empty
if (!$data && !empty($_POST)) {
    $data = $_POST;
}

// Log for debugging
file_put_contents('debug_attendance.log', date('Y-m-d H:i:s') . " Raw: " . $rawInput . " | Parsed: " . print_r($data, true) . "\n", FILE_APPEND);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit();
}

$action = $data['action'] ?? '';
$trainerId = isset($data['trainer_id']) ? intval($data['trainer_id']) : 0;

if ($trainerId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid trainer ID']);
    exit();
}

if ($action === 'clock_out') {
    $trainerId = intval($data['trainer_id']);
    
    // Get the most recent check-in record that hasn't been clocked out
    $stmt = $conn->prepare("SELECT id FROM trainer_attendance WHERE trainer_id = ? AND status = 'checked_in' ORDER BY check_in_time DESC LIMIT 1");
    $stmt->bind_param("i", $trainerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $attendanceId = $row['id'];
        
        // Update the attendance record
        $updateStmt = $conn->prepare("UPDATE trainer_attendance SET check_out_time = NOW(), status = 'checked_out' WHERE id = ?");
        $updateStmt->bind_param("i", $attendanceId);
        
        if ($updateStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Trainer clocked out successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update clock out time']);
        }
        $updateStmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No active check-in found for this trainer']);
    }
    
    $stmt->close();
} elseif ($action === 'clock_in') {
    $trainerId = intval($data['trainer_id']);
    
    // Check if trainer is already clocked in
    $checkStmt = $conn->prepare("SELECT id FROM trainer_attendance WHERE trainer_id = ? AND status = 'checked_in'");
    $checkStmt->bind_param("i", $trainerId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Trainer is already clocked in']);
    } else {
        // Create new check-in record
        $insertStmt = $conn->prepare("INSERT INTO trainer_attendance (trainer_id, check_in_time, status) VALUES (?, NOW(), 'checked_in')");
        $insertStmt->bind_param("i", $trainerId);
        
        if ($insertStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Trainer clocked in successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to clock in trainer']);
        }
        $insertStmt->close();
    }
    $checkStmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

$conn->close();
?>
