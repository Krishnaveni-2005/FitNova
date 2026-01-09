<?php
require 'db_connect.php';

// Create client_progress table
$sql = "CREATE TABLE IF NOT EXISTS client_progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    log_date DATE NOT NULL,
    current_weight DECIMAL(5,2),
    status_update VARCHAR(50) DEFAULT 'On Track', -- Excelling, On Track, Maintenance, Needs Attention
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

if ($conn->query($sql)) {
    echo "Table client_progress created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Check if we have clients to seed
$clients = $conn->query("SELECT user_id, first_name FROM users WHERE role = 'client' OR role='free' OR role='pro' LIMIT 5");
if ($clients->num_rows > 0) {
    while($client = $clients->fetch_assoc()) {
        $uid = $client['user_id'];
        // Check if progress exists
        $check = $conn->query("SELECT * FROM client_progress WHERE user_id = $uid");
        if ($check->num_rows == 0) {
            // Insert dummy progress
            $conn->query("INSERT INTO client_progress (user_id, log_date, current_weight, status_update) VALUES ($uid, CURDATE(), 70.5, 'On Track')");
            echo "Seeded progress for " . $client['first_name'] . "<br>";
        }
    }
}
?>
