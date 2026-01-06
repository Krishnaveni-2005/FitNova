<?php
require 'db_connect.php';

// Create gym_equipment table
$sql = "CREATE TABLE IF NOT EXISTS gym_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    total_units INT NOT NULL DEFAULT 0,
    available_units INT NOT NULL DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Available',
    icon VARCHAR(50) DEFAULT 'fas fa-dumbbell',
    color_class VARCHAR(20) DEFAULT 'success',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table gym_equipment created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Check if table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM gym_equipment");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert initial data
    $insertSql = "INSERT INTO gym_equipment (name, total_units, available_units, status, icon, color_class) VALUES
    ('Treadmills', 12, 8, 'High Availability', 'fas fa-running', 'success'),
    ('Free Weights', 20, 4, 'Busy Session', 'fas fa-dumbbell', 'warning'),
    ('Bench Press', 5, 0, 'Full Capacity', 'fas fa-weight-hanging', 'danger'),
    ('Squat Racks', 4, 2, 'Moderate', 'fas fa-child', 'warning'),
    ('Rowing Machines', 6, 6, 'Open', 'fas fa-water', 'success'),
    ('Lat Pulldown', 3, 1, 'Limited', 'fas fa-level-down-alt', 'warning')";

    if ($conn->query($insertSql) === TRUE) {
        echo "Initial equipment data inserted successfully.<br>";
    } else {
        echo "Error inserting data: " . $conn->error . "<br>";
    }
} else {
    echo "Table already has data.<br>";
}

$conn->close();
?>
