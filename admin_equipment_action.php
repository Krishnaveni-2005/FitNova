<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

if ($action === 'update_status') {
    $id = intval($data['id']);
    $available = intval($data['available']);
    
    // Fetch total units for valididation and status calculation
    $stmt = $conn->prepare("SELECT total_units FROM gym_equipment WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Equipment not found']);
        exit();
    }
    
    $row = $result->fetch_assoc();
    $total = $row['total_units'];
    
    if ($available < 0) $available = 0;
    if ($available > $total) $available = $total;
    
    // Determine status and color
    $ratio = $available / $total;
    $status = 'High Availability';
    $color = 'success';
    
    if ($available == 0) {
        $status = 'Full Capacity';
        $color = 'danger';
    } elseif ($ratio < 0.25) {
        $status = 'Limited Availability';
        $color = 'warning';
    } elseif ($ratio < 0.5) {
        $status = 'Busy Session';
        $color = 'warning';
    }
    
    // Update DB
    $updateStmt = $conn->prepare("UPDATE gym_equipment SET available_units = ?, status = ?, color_class = ? WHERE id = ?");
    $updateStmt->bind_param("issi", $available, $status, $color, $id);
    
    if ($updateStmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Equipment status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
    }
    
    $stmt->close();
    $updateStmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

$conn->close();
?>
