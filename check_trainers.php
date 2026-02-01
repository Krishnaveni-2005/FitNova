<?php
require 'db_connect.php';

// Fetch all trainers with their specializations
$sql = "SELECT user_id, first_name, last_name, specialization, bio FROM users WHERE role = 'trainer' AND account_status = 'active'";
$result = $conn->query($sql);

echo "Current Trainers in Database:\n";
echo str_repeat("=", 80) . "\n\n";

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['user_id'] . "\n";
        echo "Name: " . $row['first_name'] . " " . $row['last_name'] . "\n";
        echo "Specialization: " . ($row['specialization'] ?? 'Not set') . "\n";
        echo "Bio: " . ($row['bio'] ?? 'Not set') . "\n";
        echo str_repeat("-", 80) . "\n\n";
    }
} else {
    echo "No trainers found.\n";
}

$conn->close();
?>
