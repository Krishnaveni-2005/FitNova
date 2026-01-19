<?php
require "db_connect.php";

echo "<h2>Debug Match</h2>";

// 1. Find User
echo "<h3>Users matching 'Krishna'</h3>";
$sql = "SELECT user_id, first_name, last_name FROM users WHERE first_name LIKE '%Krishna%' OR last_name LIKE '%Krishna%'";
$res = $conn->query($sql);
$userId = 0;
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo "ID: " . $row['user_id'] . "<br>";
        echo "First: '" . $row['first_name'] . "'<br>";
        echo "Last: '" . $row['last_name'] . "'<br>";
        echo "Full Constructed: '" . $row['first_name'] . ' ' . $row['last_name'] . "'<br><br>";
        $userId = $row['user_id']; // Take the last one found for testing
    }
} else {
    echo "No user found.<br>";
}

// 2. Find Schedules
echo "<h3>Schedules matching 'Krishna'</h3>";
$sql2 = "SELECT * FROM trainer_schedules WHERE client_name LIKE '%Krishna%'";
$res2 = $conn->query($sql2);
if ($res2->num_rows > 0) {
    while($row = $res2->fetch_assoc()) {
        echo "Schedule ID: " . $row['schedule_id'] . "<br>";
        echo "Client Name in DB: '" . $row['client_name'] . "'<br>";
        echo "Date: " . $row['session_date'] . "<br>";
        echo "Status: " . $row['status'] . "<br><br>";
    }
} else {
    echo "No schedules found.<br>";
}

?>
