<?php
require 'db_connect.php';
$res = $conn->query("SELECT * FROM gym_equipment");
echo "Equipment Count: " . $res->num_rows . "<br>";
while ($row = $res->fetch_assoc()) {
    echo $row['name'] . ": " . $row['available_units'] . "/" . $row['total_units'] . "<br>";
}
?>
