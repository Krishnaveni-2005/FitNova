<?php
require "db_connect.php";
ini_set('display_errors', 1);

echo "<h3>Users Detail</h3>";
$res = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE first_name LIKE '%Krishna%'");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['user_id'] . "<br>";
    echo "First: [" . $row['first_name'] . "]<br>";
    echo "Last: [" . $row['last_name'] . "]<br>";
    echo "----------------<br>";
}

echo "<h3>Schedules Detail</h3>";
$resS = $conn->query("SELECT schedule_id, client_name, session_type FROM trainer_schedules");
while ($row = $resS->fetch_assoc()) {
    echo "ID: " . $row['schedule_id'] . "<br>";
    echo "Client: [" . $row['client_name'] . "]<br>";
    echo "Type: " . $row['session_type'] . "<br>";
    echo "----------------<br>";
}
?>
