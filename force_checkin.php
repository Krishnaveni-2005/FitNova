<?php
require 'db_connect.php';

// List of trainer emails to check in
$trainer_emails = [
    'davidjohn2028@mca.ajce.in',
    'diljithshaji@gmail.com',
    'joshuajoseph2028@mca.ajce.in'
];

foreach ($trainer_emails as $email) {
    // Get user id
    $res = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $uid = $user['user_id'];
        
        // Check if already checked in today
        $check = $conn->query("SELECT * FROM trainer_attendance WHERE trainer_id = $uid AND DATE(check_in_time) = CURDATE()");
        
        if ($check->num_rows > 0) {
            // Update
            $conn->query("UPDATE trainer_attendance SET status='checked_in', check_in_time=NOW() WHERE trainer_id = $uid AND DATE(check_in_time) = CURDATE()");
            echo "Updated check-in for $email<br>";
        } else {
            // Insert
            $conn->query("INSERT INTO trainer_attendance (trainer_id, check_in_time, status) VALUES ($uid, NOW(), 'checked_in')");
            echo "Inserted check-in for $email<br>";
        }
    } else {
        echo "User not found: $email<br>";
    }
}

echo "Done.";
?>
