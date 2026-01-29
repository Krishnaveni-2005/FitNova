<?php
require 'db_connect.php';

// 1. Create Gym Settings Table
$sql = "CREATE TABLE IF NOT EXISTS gym_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'gym_settings' ready.<br>";
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
    $check = $conn->query("SELECT setting_id FROM gym_settings WHERE setting_key='$key'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO gym_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->bind_param("ss", $key, $val);
        if ($stmt->execute()) {
            echo "Inserted default for $key.<br>";
        } else {
            echo "Error inserting $key: " . $stmt->error . "<br>";
        }
    } else {
        echo "$key already exists.<br>";
    }
}

// 3. Ensure Equipment Table exists (just in case)
$sqlEq = "CREATE TABLE IF NOT EXISTS gym_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon_class VARCHAR(50) DEFAULT 'fas fa-dumbbell',
    total_units INT DEFAULT 0,
    available_units INT DEFAULT 0,
    status ENUM('available', 'busy', 'unavailable') DEFAULT 'available',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sqlEq);

echo "Database setup complete.";
?>
