<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "db_connect.php";

echo "<h2>Database Dump</h2>";

$res = $conn->query("SELECT user_id, first_name, last_name, email, role FROM users LIMIT 10");
if ($res) {
    if ($res->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>First</th><th>Last</th><th>Full Name Check</th></tr>";
        while ($row = $res->fetch_assoc()) {
           echo "<tr>";
           echo "<td>" . $row['user_id'] . "</td>";
           echo "<td>" . $row['first_name'] . "</td>";
           echo "<td>" . $row['last_name'] . "</td>"; 
           echo "<td>Check</td>";
           echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Users table empty.";
    }
} else {
    echo "Query failed: " . $conn->error;
}

echo "<h3>Schedules Dump</h3>";
$res2 = $conn->query("SELECT * FROM trainer_schedules LIMIT 10");
if ($res2) {
    if ($res2->num_rows > 0) {
        while ($row = $res2->fetch_assoc()) {
            echo "Client Name: " . $row['client_name'] . "<br>";
        }
    } else {
        echo "Schedules table empty.";
    }
} else {
    echo "Query failed: " . $conn->error;
}
?>
