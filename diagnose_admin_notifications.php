<?php
require_once 'config.php';
require_once 'db_connect.php';
echo "<p>DEBUG: About to include notification_helper.php</p>";
include 'notification_helper.php'; 
echo "<p>DEBUG: Finished including notification_helper.php</p>";

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Admin Notification Diagnosis</h1>";

// 1. Check Admin User
echo "<h3>1. Checking Admin User in Database</h3>";
$adminEmail = 'krishnavenirnair2005@gmail.com';
$sql = "SELECT user_id, email, phone FROM users WHERE email = '$adminEmail'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p style='color:green'>Found User ID: " . $user['user_id'] . "</p>";
    echo "<p>Email: " . $user['email'] . "</p>";
} else {
    echo "<p style='color:red'>Admin User NOT FOUND for email: $adminEmail</p>";
    echo "<p>Please ensure there is a user in the 'users' table with this email.</p>";
}

// 2. Test sendAdminNotification Function
echo "<h3>2. Testing sendAdminNotification Function</h3>";
echo "<p>Attempting to send a test notification...</p>";

// Use sendAdminNotification
if (function_exists('sendAdminNotification')) {
    $msg = "Diagnostic Test Message at " . date('H:i:s');
    // Calling the function
    // Note: The function returns void/null based on current implementation, creating side effects
    // modifying it to return success/fail would be better but for now let's just see if it runs without error
    try {
        sendAdminNotification($conn, $msg);
        echo "<p style='color:green'>Function executed without fatal error.</p>";
        echo "<p>Check your WhatsApp now for: '$msg'</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>Function sendAdminNotification does not exist!</p>";
}

?>
