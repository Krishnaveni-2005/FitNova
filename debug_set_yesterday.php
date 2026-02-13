<?php
session_start();
require 'db_connect.php';

// Force timezone
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$userId = $_SESSION['user_id'];
$yesterday = date('Y-m-d', strtotime('-1 day'));

// 1. Update last_login to yesterday
$sql = "UPDATE user_gamification_stats SET last_login_date = '$yesterday', current_streak = 1 WHERE user_id = $userId";

if ($conn->query($sql)) {
    echo "<h3>Success! Modified your last login to: $yesterday</h3>";
    echo "<p>Your current streak is now set to 1 (as if you logged in yesterday).</p>";
    echo "<p><b>Action:</b> Go back to your <a href='freeuser_dashboard.php'>Dashboard</a> now. The system will see you logged in 'Today' vs 'Yesterday' and increment your streak to 2!</p>";
} else {
    echo "Error: " . $conn->error;
}
?>
