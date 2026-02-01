<?php
require 'db_connect.php';

echo "User Count Verification\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Total users count
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $result->fetch_assoc()['total'];
echo "Total Registered Users: $totalUsers\n\n";

// 2. Breakdown by role
echo "User Breakdown by Role:\n";
echo str_repeat("-", 80) . "\n";
$result = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role ORDER BY count DESC");
while ($row = $result->fetch_assoc()) {
    echo sprintf("  %-15s: %d users\n", ucfirst($row['role']), $row['count']);
}

// 3. Breakdown by account status
echo "\nUser Breakdown by Status:\n";
echo str_repeat("-", 80) . "\n";
$result = $conn->query("SELECT account_status, COUNT(*) as count FROM users GROUP BY account_status");
while ($row = $result->fetch_assoc()) {
    echo sprintf("  %-15s: %d users\n", ucfirst($row['account_status']), $row['count']);
}

// 4. List all users
echo "\nAll Registered Users:\n";
echo str_repeat("-", 80) . "\n";
$result = $conn->query("SELECT user_id, first_name, last_name, email, role, account_status, created_at FROM users ORDER BY created_at DESC");
$count = 1;
while ($row = $result->fetch_assoc()) {
    echo "$count. " . $row['first_name'] . " " . $row['last_name'] . "\n";
    echo "   Email: " . $row['email'] . "\n";
    echo "   Role: " . ucfirst($row['role']) . " | Status: " . ucfirst($row['account_status']) . "\n";
    echo "   Joined: " . date('M d, Y', strtotime($row['created_at'])) . "\n";
    echo str_repeat("-", 80) . "\n";
    $count++;
}

echo "\n✓ TOTAL REGISTERED USERS: $totalUsers\n";

$conn->close();
?>
