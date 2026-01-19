<?php
require "db_connect.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Connected to: " . $dbname . "<br>";

$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "Tables:<br>";
    while ($row = $result->fetch_array()) {
        echo $row[0] . "<br>";
    }
} else {
    echo "Error showing tables: " . $conn->error;
}

echo "<hr>";
$u = $conn->query("SELECT count(*) as c FROM users");
$row = $u->fetch_assoc();
echo "User count: " . $row['c'] . "<br>";

$s = $conn->query("SELECT count(*) as c FROM trainer_schedules");
$rowS = $s->fetch_assoc();
echo "Schedule count: " . $rowS['c'] . "<br>";

echo "<hr>";
echo "Checking specific user 'Krishnaveni':<br>";
$k = $conn->query("SELECT * FROM users WHERE first_name LIKE '%Krishna%'");
while ($row = $k->fetch_assoc()) {
    echo "Found: " . $row['first_name'] . " " . $row['last_name'] . " (ID: " . $row['user_id'] . ")<br>";
}
?>
