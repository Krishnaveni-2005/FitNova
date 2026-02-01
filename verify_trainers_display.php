<?php
require 'db_connect.php';

// Fetch all real trainers from database with their specializations and bios
$sql = "SELECT user_id, first_name, last_name, trainer_specialization, bio FROM users WHERE role = 'trainer' AND account_status = 'active' ORDER BY first_name";
$result = $conn->query($sql);

echo "Trainers that will be displayed on trainers.php:\n";
echo str_repeat("=", 100) . "\n\n";

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $specialization = $row['trainer_specialization'] ?? 'Personal Trainer';
        $bio = $row['bio'] ?? 'Expert in guiding clients to achieve their personal fitness goals through customized plans.';
        
        echo "Name: " . $row['first_name'] . " " . $row['last_name'] . "\n";
        echo "Specialization: " . $specialization . "\n";
        echo "Bio: " . substr($bio, 0, 100) . (strlen($bio) > 100 ? '...' : '') . "\n";
        echo str_repeat("-", 100) . "\n\n";
    }
} else {
    echo "No active trainers found.\n";
}

$conn->close();
?>
