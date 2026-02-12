<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Please log in first"]);
    exit();
}

$userId = $_SESSION['user_id'];
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$plan = $conn->real_escape_string($data['plan'] ?? '');
$billing = $conn->real_escape_string($data['billing'] ?? '');
$razorpayPaymentId = $conn->real_escape_string($data['razorpay_payment_id'] ?? '');
$razorpayOrderId = $conn->real_escape_string($data['razorpay_order_id'] ?? '');

if (empty($plan)) {
    echo json_encode(["status" => "error", "message" => "Invalid plan selected"]);
    exit();
}

// Calculate payment amounts
$plans = [
    'lite' => ['name' => 'Lite Member', 'monthly' => 2499, 'yearly' => 7999],
    'pro' => ['name' => 'Pro Member', 'monthly' => 4999, 'yearly' => 8999]
];

$selectedPlan = $plans[$plan];
$baseAmount = ($billing === 'yearly') ? $selectedPlan['yearly'] : $selectedPlan['monthly'];
$taxAmount = $baseAmount * 0.18;
$totalAmount = $baseAmount + $taxAmount;

// Map plans to roles
$role = "free";
if ($plan === 'lite') $role = 'lite';
if ($plan === 'pro') $role = 'pro';
if ($plan === 'elite') $role = 'elite';

// Update the user's role in the database
$updateSql = "UPDATE users SET role = ? WHERE user_id = ?";
$stmt = $conn->prepare($updateSql);
$stmt->bind_param("si", $role, $userId);

if ($stmt->execute()) {
    $_SESSION['user_role'] = $role; // Update session
    $message = "Plan updated successfully";
    
    // Generate unique receipt number
    $receiptNumber = 'FN' . date('Ymd') . str_pad($userId, 4, '0', STR_PAD_LEFT) . rand(1000, 9999);
    
    // Save payment receipt to database
    $receiptSql = "INSERT INTO payment_receipts (user_id, plan_name, billing_cycle, base_amount, tax_amount, total_amount, razorpay_payment_id, razorpay_order_id, receipt_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $receiptStmt = $conn->prepare($receiptSql);
    $receiptStmt->bind_param("issdddsss", $userId, $selectedPlan['name'], $billing, $baseAmount, $taxAmount, $totalAmount, $razorpayPaymentId, $razorpayOrderId, $receiptNumber);
    
    $receiptId = null;
    if ($receiptStmt->execute()) {
        $receiptId = $conn->insert_id;
    }
    $receiptStmt->close();
    
    // Handle Automatic Trainer Hire Request
    $trainerId = isset($data['trainer_id']) ? intval($data['trainer_id']) : 0;
    // Only process trainer request if role is NOT lite
    if ($trainerId > 0 && $role !== 'lite') {
        $hireSql = "UPDATE users SET assigned_trainer_id = ?, assignment_status = 'pending' WHERE user_id = ?";
        $hireStmt = $conn->prepare($hireSql);
        $hireStmt->bind_param("ii", $trainerId, $userId);
        if ($hireStmt->execute()) {
            $message .= " and Trainer Request Sent!";
            
            // Create notification for the client
            require_once 'notification_helper.php';
            
            // Get trainer name
            $trainerSql = "SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE user_id = ?";
            $tStmt = $conn->prepare($trainerSql);
            $tStmt->bind_param("i", $trainerId);
            $tStmt->execute();
            $trainerName = $tStmt->get_result()->fetch_assoc()['name'];
            $tStmt->close();
            
            $notifMessage = "Request sent to Coach " . $trainerName . ". Approval pending.";
            createNotification($conn, $userId, 'trainer_request_pending', $notifMessage);
        }
        $hireStmt->close();
    }

    // Return success with receipt ID - redirect directly to receipt
    echo json_encode([
        "status" => "success", 
        "message" => $message, 
        "redirect" => "payment_receipt.php?id=" . $receiptId
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
