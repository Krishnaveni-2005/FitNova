<?php
require 'db_connect.php';

// Check if table exists
$check = $conn->query("SHOW TABLES LIKE 'trainer_schedules'");
if ($check->num_rows == 0) {
    // Create it
    $sql = "CREATE TABLE trainer_schedules (
        schedule_id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_id INT NOT NULL,
        client_name VARCHAR(100),
        session_date DATE NOT NULL,
        session_time TIME NOT NULL,
        session_type VARCHAR(50) DEFAULT 'Training Session',
        status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trainer_id) REFERENCES users(user_id)
    )";
    if ($conn->query($sql)) {
        echo "Table trainer_schedules created.";
    } else {
        echo "Error creating table: " . $conn->error;
    }
} else {
    // Check columns
    $cols = $conn->query("SHOW COLUMNS FROM trainer_schedules");
    while($row = $cols->fetch_assoc()) {
        echo $row['Field'] . " ";
    }
}
?>
