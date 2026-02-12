<?php
require_once 'config.php';
require_once 'db_connect.php';

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnostic Test v2</h1>";

// Define function INLINE to debug logic
function sendAdminNotificationTEST($conn, $msg) {
    echo "<h3>Function sendAdminNotificationTEST called with: $msg</h3>";
    
    $adminEmail = 'krishnavenirnair2005@gmail.com';
    echo "<p>Searching for admin email: '$adminEmail'</p>";
    
    // Explicitly use proper query
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<p style='color:red'>Prepare failed: " . $conn->error . "</p>";
        return;
    }
    
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $adminId = $row['user_id'];
        echo "<p style='color:green'>Found User ID: $adminId</p>";
        
        // WhatsApp Logic from scratch
        if (defined('ADMIN_WHATSAPP_NUMBER') && defined('TWILIO_SID')) {
            echo "<p>Twilio Config Found: " . TWILIO_SID . "</p>";
            echo "<p>Sending to: " . ADMIN_WHATSAPP_NUMBER . "</p>";
            
            // Try including helper if needed, or implement curl here
            if (file_exists('twilio_helper.php')) {
                require_once 'twilio_helper.php';
                $result = sendWhatsAppNotification($msg);
                echo "<p>WhatsApp Result: " . ($result ? 'SUCCESS' : 'FAILURE') . "</p>";
            } else {
                echo "<p style='color:red'>twilio_helper.php missing</p>";
            }
        } else {
            echo "<p style='color:red'>Twilio Constants Missing</p>";
        }
    } else {
        echo "<p style='color:red'>Admin User NOT FOUND via prepare!</p>";
        
        // Debug via direct query
        $direct = $conn->query("SELECT user_id, email FROM users WHERE email = '$adminEmail'");
        if ($direct->num_rows > 0) {
            echo "<p>Direct query FOUND it! Weird.</p>";
        } else {
            echo "<p>Direct query also NOT found.</p>";
        }
    }
}

// Execute
sendAdminNotificationTEST($conn, "Test Message Inline Function");
?>
