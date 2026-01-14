<?php
require 'db_connect.php';
$res = $conn->query("SELECT count(*) as c FROM users WHERE role='trainer'");
$row = $res->fetch_assoc();
echo "Trainers Count: " . $row['c'];
?>
