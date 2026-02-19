<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

// Check Admin Auth
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    // Optional: delivery date or notes could be added here later

    if (!$order_id || empty($status)) {
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE shop_orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Order status updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
}

$conn->close();
?>
