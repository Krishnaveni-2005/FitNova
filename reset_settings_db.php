<?php
require 'db_connect.php';

// Force Reset to ensure correct schema
$conn->query("DROP TABLE IF EXISTS gym_settings");

// 1. Create Gym Settings Table
$sql = "CREATE TABLE gym_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'gym_settings' created.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 2. Insert Defaults
$defaults = [
    'gym_open_time' => '06:00 AM',
    'gym_close_time' => '10:00 PM',
    'gym_status' => 'open'
];

foreach ($defaults as $key => $val) {
    $stmt = $conn->prepare("INSERT INTO gym_settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->bind_param("ss", $key, $val);
    if ($stmt->execute()) {
        echo "Inserted default for $key.<br>";
    } else {
        echo "Error inserting $key: " . $stmt->error . "<br>";
    }
}

echo "Database settings reset and seeded.";
?>
