<?php
// notification_helper.php - Helper for general user notifications
// Restored after deletion

if (!function_exists('createNotification')) {
    function createNotification($conn, $userId, $type, $message) {
        // Check if identical notification already exists (to avoid duplicates)
        // using prepared statement
        $checkSql = "SELECT notification_id FROM user_notifications 
                     WHERE user_id = ? AND notification_type = ? AND message = ? 
                     AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $stmt = $conn->prepare($checkSql);
        if (!$stmt) {
            error_log("Prepare failed in createNotification: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("iss", $userId, $type, $message);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res->num_rows > 0;
        $stmt->close();
        
        if (!$exists) {
            $insertSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            if ($stmt) {
                $stmt->bind_param("iss", $userId, $type, $message);
                $success = $stmt->execute();
                $stmt->close();
                return $success;
            }
        }
        return false; 
    }
}
?>
