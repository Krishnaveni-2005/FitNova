<?php
// Simulate session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_email'] = 'krishnavenirnair2005@gmail.com';

// Mock request
$_POST = []; // Not used, usesphp://input
$input = json_encode(['action' => 'clock_in', 'trainer_id' => 36]); // Use a valid trainer ID

// Capture output
ob_start();
require 'admin_trainer_attendance.php';
$output = ob_get_clean();

echo "Response: " . $output . "\n";
?>
