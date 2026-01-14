<?php
// Helper function to create a notification
function createNotification($conn, $userId, $type, $message) {
    // Check if identical notification already exists (to avoid duplicates)
    $checkSql = "SELECT notification_id FROM user_notifications 
                 WHERE user_id = ? AND notification_type = ? AND message = ? 
                 AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("iss", $userId, $type, $message);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    
    if (!$exists) {
        $insertSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("iss", $userId, $type, $message);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    return false; // Already exists
}
?>
