<?php
require 'db_connect.php';

// Create expert_enquiries table
$sql = "CREATE TABLE IF NOT EXISTS expert_enquiries (
    enquiry_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'contacted', 'resolved') DEFAULT 'pending'
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'expert_enquiries' created successfully!\n";
} else {
    echo "✗ Error creating table: " . $conn->error . "\n";
}

// Check if table exists and show structure
$result = $conn->query("DESCRIBE expert_enquiries");
if ($result) {
    echo "\nTable Structure:\n";
    echo str_repeat("=", 80) . "\n";
    while($row = $result->fetch_assoc()) {
        echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
    }
}

$conn->close();
?>
