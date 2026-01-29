<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

// Security Check
$allowed_email = 'ashakayaplackal@gmail.com';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email']) || strtolower($_SESSION['user_email']) !== strtolower($allowed_email)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['type'])) {
    if ($input['type'] === 'equipment') {
        $id = $input['id'];
        $avail = $input['available_units'];
        $status = $input['status'];
        
        $stmt = $conn->prepare("UPDATE gym_equipment SET available_units=?, status=?, last_updated=NOW() WHERE id=?");
        $stmt->bind_param("isi", $avail, $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
    } 
    elseif ($input['type'] === 'settings') {
        $open = $input['open'];
        $close = $input['close'];
        $status = $input['status'];
        
        $conn->query("UPDATE gym_settings SET setting_value='$open', updated_at=NOW() WHERE setting_key='gym_open_time'");
        $conn->query("UPDATE gym_settings SET setting_value='$close', updated_at=NOW() WHERE setting_key='gym_close_time'");
        $conn->query("UPDATE gym_settings SET setting_value='$status', updated_at=NOW() WHERE setting_key='gym_status'");
        
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
$conn->close();
?>
