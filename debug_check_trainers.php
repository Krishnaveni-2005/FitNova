<?php
require 'db_connect.php';

echo "<h2>Trainer ID 4</h2>";
$sql = "SELECT user_id, first_name, last_name, email FROM users WHERE user_id = 4";
$res = $conn->query($sql);
print_r($res->fetch_assoc());

echo "<h2>Agnus</h2>";
$sql2 = "SELECT user_id, first_name, last_name, email FROM users WHERE first_name LIKE '%Agnus%'";
$res2 = $conn->query($sql2);
while($row = $res2->fetch_assoc()) {
    print_r($row);
}
?>
