<?php
require 'db_connect.php';
$res = $conn->query("SELECT user_id, first_name FROM users WHERE role='trainer' LIMIT 1");
$row = $res->fetch_assoc();
echo "Trainer ID: " . $row['user_id'] . "\n";
?>
