<?php
// admin_notifications.php - Central notification system for admin
// Ensures notifications go to:
// 1. Dashboard (DB)
// 2. WhatsApp (Twilio)

if (!function_exists('sendAdminNotification')) {
    function sendAdminNotification($conn, $message) {
        $adminEmail = 'krishnavenirnair2005@gmail.com';
        
        // Use prepared statement for security
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            error_log("Failed to prepare statement in admin_notifications.php: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("s", $adminEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $adminId = $row['user_id'];
            
            // 1. Dashboard Notification
            // Check if notification helper function exists, if not, direct query
            if (function_exists('createNotification')) {
                createNotification($conn, $adminId, 'admin_alert', $message);
            } else {
                // Fallback direct insert
                $notifSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, 'admin_alert', ?)";
                $nStmt = $conn->prepare($notifSql);
                if ($nStmt) {
                    $nStmt->bind_param("is", $adminId, $message);
                    $nStmt->execute();
                    $nStmt->close();
                }
            }
            
            // 2. WhatsApp Notification
            // Check if Twilio helper exists
            if (file_exists(__DIR__ . '/twilio_helper.php')) {
                require_once __DIR__ . '/twilio_helper.php';
                if (function_exists('sendWhatsAppNotification')) {
                    $waResult = sendWhatsAppNotification($message);
                    if (!$waResult) {
                        error_log("WhatsApp failed for admin: $adminEmail");
                    }
                }
            } else {
                 error_log("twilio_helper.php missing for admin_notifications.php");
            }
            
            error_log("Sent admin notification to $adminEmail (ID: $adminId)");
            return true;
        } else {
            error_log("Admin user not found for email: $adminEmail");
            return false;
        }
        
        $stmt->close();
    }
}
?>
