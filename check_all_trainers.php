<?php
require 'db_connect.php';

// Fetch all trainers
$sql = "SELECT user_id, first_name, last_name, trainer_specialization, bio, account_status FROM users WHERE role = 'trainer' ORDER BY account_status, first_name";
$result = $conn->query($sql);

echo "All Trainers in Database:\n";
echo str_repeat("=", 100) . "\n\n";

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['user_id'] . "\n";
        echo "Name: " . $row['first_name'] . " " . $row['last_name'] . "\n";
        echo "Status: " . $row['account_status'] . "\n";
        echo "Specialization: " . ($row['trainer_specialization'] ?? 'Not set') . "\n";
        echo "Bio: " . ($row['bio'] ?? 'Not set') . "\n";
        echo str_repeat("-", 100) . "\n\n";
    }
} else {
    echo "No trainers found.\n";
}

$conn->close();
?>
