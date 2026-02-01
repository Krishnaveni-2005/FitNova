<?php
require 'db_connect.php';

// Check current equipment data
echo "<h3>Current Equipment Data:</h3>";
$result = $conn->query("SELECT id, name, icon_class FROM gym_equipment");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Icon: {$row['icon_class']}<br>";
    }
} else {
    echo "No equipment found.<br>";
}

// Update icons if they are missing
echo "<br><h3>Updating Icons:</h3>";
$iconMapping = [
    'Treadmills' => 'fas fa-running',
    'Free Weights' => 'fas fa-dumbbell',
    'Bench Press' => 'fas fa-weight-hanging',
    'Squat Racks' => 'fas fa-child',
    'Rowing Machines' => 'fas fa-water',
    'Lat Pulldown' => 'fas fa-level-down-alt'
];

foreach ($iconMapping as $name => $icon) {
    $stmt = $conn->prepare("UPDATE gym_equipment SET icon_class = ? WHERE name = ?");
    $stmt->bind_param("ss", $icon, $name);
    if ($stmt->execute()) {
        echo "Updated icon for $name to $icon<br>";
    }
}

echo "<br><h3>Updated Equipment Data:</h3>";
$result = $conn->query("SELECT id, name, icon_class FROM gym_equipment");
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Icon: {$row['icon_class']}<br>";
    }
}

echo "<br><strong>Done! Icons have been updated.</strong>";
?>
