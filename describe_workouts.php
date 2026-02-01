<?php
require 'db_connect.php';
$result = $conn->query("DESCRIBE trainer_workouts");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
