<?php
require 'db_connect.php';

echo "Trainer Count Verification\n";
echo str_repeat("=", 80) . "\n\n";

// Active trainers (status != 'pending')
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'trainer' AND account_status != 'pending'");
$activeTrainers = $result->fetch_assoc()['count'];
echo "Active Trainers (status != 'pending'): $activeTrainers\n";

// Pending trainers
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'trainer' AND account_status = 'pending'");
$pendingTrainers = $result->fetch_assoc()['count'];
echo "Pending Trainers: $pendingTrainers\n";

// Total trainers
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'trainer'");
$totalTrainers = $result->fetch_assoc()['count'];
echo "Total Trainers: $totalTrainers\n\n";

// List all trainers
echo "All Trainers:\n";
echo str_repeat("-", 80) . "\n";
$result = $conn->query("SELECT first_name, last_name, email, account_status, trainer_type FROM users WHERE role = 'trainer' ORDER BY account_status, first_name");
$count = 1;
while ($row = $result->fetch_assoc()) {
    $status = ucfirst($row['account_status']);
    $type = ucfirst($row['trainer_type'] ?? 'N/A');
    echo "$count. " . $row['first_name'] . " " . $row['last_name'] . " - $status ($type)\n";
    echo "   Email: " . $row['email'] . "\n";
    $count++;
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✓ ACTIVE TRAINERS: $activeTrainers\n";
echo "✓ PENDING TRAINERS: $pendingTrainers\n";
echo "✓ TOTAL TRAINERS: $totalTrainers\n";

$conn->close();
?>
