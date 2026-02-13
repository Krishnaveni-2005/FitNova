<?php
session_start();
require 'db_connect.php';

// Force timezone
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

echo "<h2>Day Streak Logic Test</h2>";
echo "Current Time (Asia/Kolkata): " . date('Y-m-d H:i:s') . "<br>";
echo "Today: $today | Yesterday: $yesterday<br><hr>";

// 1. Get Current State
echo "<h3>1. Current State (Before Simulating Yesterday)</h3>";
$res = $conn->query("SELECT * FROM user_gamification_stats WHERE user_id = $userId");
if($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "Last Login Date: " . $row['last_login_date'] . "<br>";
    echo "Current Streak: " . $row['current_streak'] . "<br>";
} else {
    echo "No stats found.<br>";
}

// 2. FORCE set to Yesterday
echo "<hr><h3>2. Simulating 'Last Login = Yesterday'</h3>";
$conn->query("UPDATE user_gamification_stats SET last_login_date = '$yesterday', current_streak = 5 WHERE user_id = $userId");
echo "Updated DB: Set last_login_date to $yesterday and current_streak to 5.<br>";

// 3. Verify Update
$res = $conn->query("SELECT * FROM user_gamification_stats WHERE user_id = $userId");
$row = $res->fetch_assoc();
echo "State Now -> Last Login: " . $row['last_login_date'] . " | Streak: " . $row['current_streak'] . "<br>";

// 4. Run the Logic Function
echo "<hr><h3>3. Running checkAndAwardBadges() function...</h3>";
require_once 'gamification_helper.php';
// Note: We need to bypass the date_default_timezone_set in helper if we didn't want to reset it, but here it's fine.
checkAndAwardBadges($userId);

// 5. Final Result
echo "<h3>4. Final State (After Logic Run)</h3>";
$res = $conn->query("SELECT * FROM user_gamification_stats WHERE user_id = $userId");
$row = $res->fetch_assoc();
echo "Last Login Date: " . $row['last_login_date'] . "<br>";
echo "Current Streak: <b style='color:green; font-size:1.2em;'>" . $row['current_streak'] . "</b><br>";

if ($row['last_login_date'] == $today && $row['current_streak'] == 6) {
    echo "<h2 style='color: green;'>SUCCESS: Streak incremented from 5 to 6!</h2>";
} else {
    echo "<h2 style='color: red;'>FAILURE: Streak logic didn't work as expected.</h2>";
}
?>
