<?php
require 'db_connect.php';
$res = $conn->query("SHOW COLUMNS FROM trainer_workouts");
while($row = $res->fetch_assoc()) echo $row['Field'] . "<br>";
?>
