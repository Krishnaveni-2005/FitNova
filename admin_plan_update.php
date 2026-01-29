<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Check Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['plan_id'])) {
    $planId = intval($data['plan_id']);
    $priceMonth = floatval($data['price_monthly']);
    $priceYear = floatval($data['price_yearly']);
    // Features are passed as newline separated string, convert to JSON
    $features = array_filter(array_map('trim', explode("\n", $data['features'])));
    $featuresJson = json_encode(array_values($features));
    
    $stmt = $conn->prepare("UPDATE subscription_plans SET price_monthly = ?, price_yearly = ?, features = ? WHERE plan_id = ?");
    $stmt->bind_param("ddsi", $priceMonth, $priceYear, $featuresJson, $planId);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Plan updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>
