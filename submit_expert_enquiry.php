<?php
header('Content-Type: application/json');
require 'db_connect.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    
    // Server-side validation
    if (empty($name) || empty($phone) || empty($email) || empty($reason)) {
        $response['message'] = 'All fields are required!';
        echo json_encode($response);
        exit;
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address!';
        echo json_encode($response);
        exit;
    }
    
    // Phone validation (10 digits)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $response['message'] = 'Please enter a valid 10-digit phone number!';
        echo json_encode($response);
        exit;
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO expert_enquiries (name, phone, email, reason) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $reason);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Enquiry submitted successfully!';
    } else {
        $response['message'] = 'Database error. Please try again later.';
    }
    
    $stmt->close();

    // Send Notification only on success
    if ($response['success']) {
        require_once 'admin_notifications.php';
        $adminMsg = "New Expert Enquiry from $name ($phone): " . substr($reason, 0, 50) . "...";
        if (function_exists('sendAdminNotification')) {
            sendAdminNotification($conn, $adminMsg);
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>
