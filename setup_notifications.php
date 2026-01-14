<?php
require 'db_connect.php';

// Create notifications table
$sql = "CREATE TABLE IF NOT EXISTS user_notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type VARCHAR(50),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

if ($conn->query($sql)) {
    echo "Notifications table created successfully.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

// Now let's create a notification for existing trainer assignments
$checkSql = "SELECT user_id, assigned_trainer_id, assignment_status, 
             (SELECT CONCAT(first_name, ' ', last_name) FROM users WHERE user_id = u.assigned_trainer_id) as trainer_name
             FROM users u 
             WHERE assigned_trainer_id IS NOT NULL AND assignment_status != 'none'";

$result = $conn->query($checkSql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userId = $row['user_id'];
        $status = $row['assignment_status'];
        $trainerName = $row['trainer_name'];
        
        $message = '';
        $type = '';
        
        if ($status === 'pending') {
            $message = "Request sent to Coach " . $trainerName . ". Approval pending.";
            $type = 'trainer_request_pending';
        } elseif ($status === 'approved') {
            $message = "Great news! Coach " . $trainerName . " has approved your request.";
            $type = 'trainer_request_approved';
        } elseif ($status === 'rejected') {
            $message = "Your request to Coach " . $trainerName . " was declined.";
            $type = 'trainer_request_rejected';
        }
        
        if ($message) {
            // Check if notification already exists
            $checkNotif = "SELECT notification_id FROM user_notifications WHERE user_id = ? AND notification_type = ? AND message = ?";
            $stmt = $conn->prepare($checkNotif);
            $stmt->bind_param("iss", $userId, $type, $message);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            
            if (!$exists) {
                $insertSql = "INSERT INTO user_notifications (user_id, notification_type, message) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insertSql);
                $stmt->bind_param("iss", $userId, $type, $message);
                if ($stmt->execute()) {
                    echo "Created notification for user $userId: $message\n";
                }
                $stmt->close();
            }
        }
    }
}

echo "Setup complete!\n";
?>
