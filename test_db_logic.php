<?php
require 'db_connect.php';
$trainerId = 36; 

echo "Testing DB Logic for Trainer ID: $trainerId\n";

// 1. Manually Insert Check-in
$conn->query("INSERT INTO trainer_attendance (trainer_id, check_in_time, status) VALUES ($trainerId, NOW(), 'checked_in')");
if ($conn->errno) {
    echo "Insert failed: " . $conn->error . "\n";
} else {
    echo "Insert successful. ID: " . $conn->insert_id . "\n";
}

// 2. Check if it appears in the select query used by onsite_trainers.php
$sql = "SELECT u.first_name, 
        (SELECT status FROM trainer_attendance ta 
         WHERE ta.trainer_id = u.user_id AND DATE(ta.check_in_time) = CURDATE() 
         ORDER BY ta.check_in_time DESC LIMIT 1) as attendance_status
        FROM users u 
        WHERE u.user_id = $trainerId";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
echo "Status in Query: " . $row['attendance_status'] . "\n";

// 3. Cleanup (Clock out)
$conn->query("UPDATE trainer_attendance SET status = 'checked_out', check_out_time = NOW() WHERE trainer_id = $trainerId AND status = 'checked_in'");
echo "Cleaned up.\n";
?>
