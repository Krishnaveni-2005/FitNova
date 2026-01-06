<?php
require "db_connect.php";

echo "Checking database connection...<br>";

// Simulate logic from prouser_dashboard.php
$userName = "Test User"; // distinct from real logic but enough for prepare test

// Query 1 (Profile)
$profileSql = "SELECT * FROM client_profiles WHERE user_id = ?";
$stmt = $conn->prepare($profileSql);
if (!$stmt) { echo "Profile prepare failed: " . $conn->error . "\n"; }
else {
    $uid = 1;
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) $res->fetch_assoc();
    // stmt not closed here in original code
}

// Query 2 (Workouts)
$workoutSql = "SELECT * FROM trainer_workouts WHERE client_name = ? ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($workoutSql);
if (!$stmt) { echo "Workout prepare failed: " . $conn->error . "\n"; }
else {
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    // stmt not closed
}

// Query 3 (Diet)
$dietSql = "SELECT * FROM trainer_diet_plans WHERE client_name = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($dietSql); // This is where original failed
if (!$stmt) {
    echo "Diet prepare failed: " . $conn->error . "\n";
} else {
    echo "Diet prepare successful.\n";
}
?>
