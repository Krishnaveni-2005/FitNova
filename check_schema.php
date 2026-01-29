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

echo "<h3>Adding Missing Columns if needed...</h3>";

// Add gym_membership_status
$conn->query("ALTER TABLE users ADD COLUMN gym_membership_status ENUM('active', 'inactive') DEFAULT 'inactive'");

// Add user_id to diet/workout if missing (re-run safe)
$conn->query("ALTER TABLE trainer_diet_plans ADD COLUMN user_id INT AFTER trainer_id");
$conn->query("ALTER TABLE trainer_workouts ADD COLUMN user_id INT AFTER trainer_id");

// Verify again
echo "<h3>Re-verification:</h3>";
checkColumn($conn, 'users', 'gym_membership_status');
checkColumn($conn, 'trainer_diet_plans', 'user_id');

?>
