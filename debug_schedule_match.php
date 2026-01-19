<?php
require "db_connect.php";

echo "<h2>Debug: Trainer Schedules & Users</h2>";

// 1. List all schedules
echo "<h3>All Trainer Schedules</h3>";
$res = $conn->query("SELECT * FROM trainer_schedules ORDER BY created_at DESC LIMIT 5");
if ($res->num_rows > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Trainer ID</th><th>Client Name</th><th>Session Type</th><th>Date</th><th>Time</th><th>Status</th></tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['schedule_id'] . "</td>";
        echo "<td>" . $row['trainer_id'] . "</td>";
        echo "<td>'" . $row['client_name'] . "'</td>"; // Single quotes to see whitespace
        echo "<td>" . $row['session_type'] . "</td>";
        echo "<td>" . $row['session_date'] . "</td>";
        echo "<td>" . $row['session_time'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No schedules found.";
}

// 2. List Users matching 'Krishnaveni' or similar
echo "<h3>Users (matching 'Krishna')</h3>";
$res = $conn->query("SELECT user_id, first_name, last_name, CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE first_name LIKE '%Krishna%' OR last_name LIKE '%Krishna%'");
if ($res->num_rows > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>User ID</th><th>First Name</th><th>Last Name</th><th>Full Name (CONCAT)</th></tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>'" . $row['first_name'] . "'</td>";
        echo "<td>'" . $row['last_name'] . "'</td>";
        echo "<td>'" . $row['full_name'] . "'</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found matching 'Krishna'.";
}
?>
