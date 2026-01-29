<?php
require 'db_connect.php';

// 1. Create Equipment Table
$sql = "CREATE TABLE IF NOT EXISTS gym_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon_class VARCHAR(50) DEFAULT 'fas fa-dumbbell',
    total_units INT DEFAULT 0,
    available_units INT DEFAULT 0,
    status ENUM('available', 'busy', 'unavailable') DEFAULT 'available',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'gym_equipment' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 2. Populate Initial Data (from hardcoded gym.php values)
$equipment = [
    ['Treadmills', 'fas fa-running', 12, 8, 'available'],
    ['Free Weights', 'fas fa-dumbbell', 20, 4, 'busy'],
    ['Bench Press', 'fas fa-weight-hanging', 5, 0, 'unavailable'],
    ['Squat Racks', 'fas fa-child', 4, 2, 'busy'],
    ['Rowing Machines', 'fas fa-water', 6, 6, 'available'],
    ['Lat Pulldown', 'fas fa-level-down-alt', 3, 1, 'busy']
];

foreach ($equipment as $item) {
    $name = $item[0];
    $check = $conn->query("SELECT id FROM gym_equipment WHERE name='$name'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO gym_equipment (name, icon_class, total_units, available_units, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiis", $item[0], $item[1], $item[2], $item[3], $item[4]);
        $stmt->execute();
        echo "Inserted $name.<br>";
    }
}
echo "Equipment data populated.<br>";
?>
