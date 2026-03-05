<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$userId = $_SESSION['user_id'];
$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if ($orderId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit();
}

// Check if the order belongs to the user and is within 10 days
$stmt = $conn->prepare("SELECT order_date, order_status FROM shop_orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $orderStatus = $row['order_status'];
    $orderDate = new DateTime($row['order_date']);
    $now = new DateTime();
    $diff = $now->diff($orderDate);

    // If order already returned or in process
    if (in_array($orderStatus, ['Return Requested', 'Returned', 'Cancelled'])) {
        echo json_encode(['status' => 'error', 'message' => 'Return already in process or not allowed.']);
        exit();
    }

    // Checking if within 10 days
    if ($diff->days <= 10) {
        $reason = isset($_POST['reason']) ? $_POST['reason'] : 'Not specified';
        $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
        
        $fullReason = $reason;
        if (!empty($comment)) {
            $fullReason .= " - " . $comment;
        }

        // Ensure the column exists in case it doesn't yet in older setups
        $conn->query("ALTER TABLE shop_orders ADD COLUMN return_reason TEXT DEFAULT NULL");

        $updateStmt = $conn->prepare("UPDATE shop_orders SET order_status = 'Return Requested', return_reason = ? WHERE order_id = ?");
        $updateStmt->bind_param("si", $fullReason, $orderId);
        
        if ($updateStmt->execute()) {
            
            // Send an admin notification (if the system supports it)
            if (file_exists('admin_notifications.php')) {
                require_once 'admin_notifications.php';
                if (function_exists('sendAdminNotification')) {
                    sendAdminNotification($conn, "Return requested for Order #$orderId by client #$userId. Reason: $reason.");
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Return requested successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error during update.']);
        }
        $updateStmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Cannot return items after 10 days.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
}

$stmt->close();
$conn->close();
?>
