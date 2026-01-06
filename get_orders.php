<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['user_id'];
// Get recent orders first
$sql = "SELECT * FROM shop_orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderId = $row['order_id'];
    // Fetch items for this order
    $itemSql = "SELECT * FROM shop_order_items WHERE order_id = ?";
    $itemStmt = $conn->prepare($itemSql);
    $itemStmt->bind_param("i", $orderId);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();
    
    $items = [];
    while ($item = $itemResult->fetch_assoc()) {
        $items[] = $item;
    }
    
    $row['items'] = $items;
    $orders[] = $row;
    $itemStmt->close(); 
}

$stmt->close();
$conn->close();

echo json_encode($orders);
?>
