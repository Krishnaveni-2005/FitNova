<?php
// admin_notifications.php - Central notification system for admin
// Ensures notifications go to:
// 1. Dashboard (DB)
// 2. WhatsApp (Twilio)

if (!function_exists('sendAdminNotification')) {
    function sendAdminNotification($conn, $message) {
        // --- 1. Dashboard Notification (Attempt) ---
        // Fetch Main Admin
        $adminEmail = 'krishnavenirnair2005@gmail.com';
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        
        if ($stmt) {
            $stmt->bind_param("s", $adminEmail);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $adminId = $row['user_id'];
                
                // Check if notification helper function exists, if not, direct query
                if (function_exists('createNotification')) {
                    createNotification($conn, $adminId, 'admin_alert', $message);
                } else {
                    $notifSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, 'admin_alert', ?)";
                    $nStmt = $conn->prepare($notifSql);
                    if ($nStmt) {
                        $nStmt->bind_param("is", $adminId, $message);
                        $nStmt->execute();
                        $nStmt->close();
                    }
                }
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare statement in admin_notifications.php for dashboard notification");
        }

        // --- 2. WhatsApp Notification (ALWAYS SEND) ---
        // Check if Twilio helper exists
        if (file_exists(__DIR__ . '/twilio_helper.php')) {
            require_once __DIR__ . '/twilio_helper.php';
            if (function_exists('sendWhatsAppNotification')) {
                // Determine Admin Number from Config or Hardcoded fallback
                $recipient = defined('ADMIN_WHATSAPP_NUMBER') ? ADMIN_WHATSAPP_NUMBER : 'whatsapp:+918078998813';
                
                $waResult = sendWhatsAppNotification($message, $recipient);
                
                // Log result
                if ($waResult) {
                    error_log("✅ WhatsApp sent successfully: $message");
                } else {
                    error_log("❌ WhatsApp FAILED for message: $message");
                }
                return $waResult;
            }
        } else {
             error_log("twilio_helper.php missing for admin_notifications.php");
        }
        
        return true;
    }
}
?>
