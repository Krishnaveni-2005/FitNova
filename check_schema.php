<?php
require 'db_connect.php';

function checkColumn($conn, $table, $column) {
    if (!$conn) return;
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($res && $res->num_rows > 0) {
        echo "Column '$column' exists in '$table'.<br>";
    } else {
        echo "Column '$column' MISSING in '$table'.<br>";
    }
}

echo "<h2>Checking Database Schema</h2>";
checkColumn($conn, 'users', 'gym_membership_status');
checkColumn($conn, 'trainer_diet_plans', 'user_id');
checkColumn($conn, 'trainer_workouts', 'user_id');
checkColumn($conn, 'users', 'assigned_trainer_id');
checkColumn($conn, 'users', 'trainer_specialization');

// Check full columns of client_profiles
echo "<h3>Full Schema of client_profiles:</h3>";
$res = $conn->query("SHOW COLUMNS FROM client_profiles");
if ($res) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    while($row = $res->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Table client_profiles probably doesn't exist.";
}

// Verify again
echo "<h3>Re-verification:</h3>";
checkColumn($conn, 'users', 'gym_membership_status');
checkColumn($conn, 'trainer_diet_plans', 'user_id');

?>
