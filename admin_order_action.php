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

    // Check current status before updating
    $currStatusQuery = $conn->prepare("SELECT order_status FROM shop_orders WHERE order_id = ?");
    $currStatusQuery->bind_param("i", $order_id);
    $currStatusQuery->execute();
    $currStatusRes = $currStatusQuery->get_result();
    $currStatus = '';
    if ($row = $currStatusRes->fetch_assoc()) {
        $currStatus = $row['order_status'];
    }
    $currStatusQuery->close();

    // Defensive column creation in case missing
    $conn->query("ALTER TABLE shop_orders ADD COLUMN admin_message TEXT DEFAULT NULL");

    $admin_message = $_POST['admin_message'] ?? '';

    $stmt = $conn->prepare("UPDATE shop_orders SET order_status = ?, admin_message = ? WHERE order_id = ?");
    $stmt->bind_param("ssi", $status, $admin_message, $order_id);

    if ($stmt->execute()) {
        // If transitioning to Returned or Cancelled and wasn't already, restore stock
        if (in_array($status, ['Returned', 'Cancelled']) && !in_array($currStatus, ['Returned', 'Cancelled'])) {
            $itemsQuery = $conn->prepare("SELECT product_id, quantity FROM shop_order_items WHERE order_id = ?");
            $itemsQuery->bind_param("i", $order_id);
            $itemsQuery->execute();
            $itemsRes = $itemsQuery->get_result();
            while ($item = $itemsRes->fetch_assoc()) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                if ($pid > 0 && $qty > 0) {
                    $stockUpd = $conn->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
                    $stockUpd->bind_param("ii", $qty, $pid);
                    $stockUpd->execute();
                    $stockUpd->close();
                }
            }
            $itemsQuery->close();
        }

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
