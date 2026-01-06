<?php
require 'db_connect.php';

// Create trainer_attendance table for shift tracking
$sql = "CREATE TABLE IF NOT EXISTS trainer_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    check_in_time DATETIME NOT NULL,
    check_out_time DATETIME DEFAULT NULL,
    zone VARCHAR(100) DEFAULT 'General Gym Floor',
    status ENUM('checked_in', 'checked_out') DEFAULT 'checked_in',
    FOREIGN KEY (trainer_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table trainer_attendance created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$conn->close();
?>
